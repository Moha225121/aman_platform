const root = document.getElementById('bookingCall');

if (root) {
    const $ = id => document.getElementById(id);
    const startButton = $('startCall');
    const remoteVideo = $('remoteVideo');
    const localVideo = $('localVideo');
    const waiting = $('callWaiting');
    const title = $('callTitle');
    const status = $('callStatus');
    const answerControls = $('callAnswer');
    let peer = null;
    let localStream = null;
    let lastSignalId = 0;
    let active = false;
    let caller = false;
    let polling = false;
    let pendingCandidates = [];

    const sendSignal = async (type, payload = {}) => {
        const response = await fetch(root.dataset.sendUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': root.dataset.csrf,
            },
            body: JSON.stringify({type, payload}),
        });
        if (!response.ok) throw new Error('تعذر إرسال إشارة المكالمة.');
    };

    const openMedia = async () => {
        if (localStream) return localStream;
        localStream = await navigator.mediaDevices.getUserMedia({
            audio: {echoCancellation: true, noiseSuppression: true},
            video: {facingMode: 'user', width: {ideal: 1280}, height: {ideal: 720}},
        });
        localVideo.srcObject = localStream;
        return localStream;
    };

    const createPeer = async () => {
        if (peer) return peer;
        peer = new RTCPeerConnection({iceServers: [{urls: 'stun:stun.l.google.com:19302'}]});
        (await openMedia()).getTracks().forEach(track => peer.addTrack(track, localStream));
        peer.ontrack = event => {
            remoteVideo.srcObject = event.streams[0];
            waiting.hidden = true;
        };
        peer.onicecandidate = event => event.candidate && sendSignal('ice', {candidate: event.candidate.toJSON()}).catch(showFailure);
        peer.onconnectionstatechange = () => {
            if (peer?.connectionState === 'connected') {
                status.textContent = 'متصل — الوسائط مشفرة من طرف إلى طرف';
                waiting.hidden = true;
            }
            if (['failed', 'disconnected'].includes(peer?.connectionState)) showFailure();
        };
        return peer;
    };

    const flushCandidates = async () => {
        if (!peer?.remoteDescription) return;
        for (const candidate of pendingCandidates.splice(0)) await peer.addIceCandidate(candidate);
    };

    const showFailure = () => {
        root.hidden = false;
        waiting.hidden = false;
        answerControls.hidden = true;
        title.textContent = 'تعذر إكمال المكالمة';
        status.textContent = 'تحقق من إذن الكاميرا والميكروفون والاتصال، ثم حاول مرة أخرى.';
    };

    const closeCall = (notify = false) => {
        if (notify && active) sendSignal('hangup').catch(() => {});
        active = false;
        caller = false;
        peer?.close();
        peer = null;
        localStream?.getTracks().forEach(track => track.stop());
        localStream = null;
        localVideo.srcObject = null;
        remoteVideo.srcObject = null;
        pendingCandidates = [];
        root.hidden = true;
        waiting.hidden = false;
        answerControls.hidden = true;
        startButton.disabled = false;
    };

    const beginCall = async () => {
        try {
            active = true;
            caller = true;
            startButton.disabled = true;
            root.hidden = false;
            waiting.hidden = false;
            title.textContent = `جاري الاتصال بـ ${root.dataset.peerName}`;
            status.textContent = 'بانتظار قبول المكالمة…';
            answerControls.hidden = true;
            await openMedia();
            await sendSignal('invite');
        } catch (error) { showFailure(error); }
    };

    const handleSignal = async signal => {
        if (signal.type === 'invite' && !active) {
            active = true;
            caller = false;
            root.hidden = false;
            waiting.hidden = false;
            title.textContent = `مكالمة فيديو من ${root.dataset.peerName}`;
            status.textContent = 'اتصال مباشر ومشفر';
            answerControls.hidden = false;
            return;
        }
        if (!active) return;
        if (signal.type === 'accept' && caller) {
            const connection = await createPeer();
            const offer = await connection.createOffer();
            await connection.setLocalDescription(offer);
            await sendSignal('offer', {description: connection.localDescription.toJSON()});
        } else if (signal.type === 'offer' && !caller) {
            const connection = await createPeer();
            await connection.setRemoteDescription(signal.payload.description);
            await flushCandidates();
            const answer = await connection.createAnswer();
            await connection.setLocalDescription(answer);
            await sendSignal('answer', {description: connection.localDescription.toJSON()});
        } else if (signal.type === 'answer' && caller && peer) {
            await peer.setRemoteDescription(signal.payload.description);
            await flushCandidates();
        } else if (signal.type === 'ice' && signal.payload.candidate) {
            if (peer?.remoteDescription) await peer.addIceCandidate(signal.payload.candidate);
            else pendingCandidates.push(signal.payload.candidate);
        } else if (['decline', 'hangup'].includes(signal.type)) {
            closeCall(false);
        }
    };

    const poll = async () => {
        if (polling) return;
        polling = true;
        try {
            const response = await fetch(`${root.dataset.signalsUrl}?after=${lastSignalId}`, {headers: {'Accept': 'application/json'}});
            if (!response.ok) return;
            const {signals} = await response.json();
            for (const signal of signals) {
                lastSignalId = Math.max(lastSignalId, signal.id);
                try { await handleSignal(signal); } catch (error) { showFailure(error); }
            }
        } finally { polling = false; }
    };

    startButton.addEventListener('click', beginCall);
    $('acceptCall').addEventListener('click', async () => {
        try {
            answerControls.hidden = true;
            title.textContent = 'جاري بدء المكالمة…';
            status.textContent = 'يتم إنشاء اتصال مباشر ومشفر…';
            await openMedia();
            await sendSignal('accept');
        } catch (error) { showFailure(error); }
    });
    $('declineCall').addEventListener('click', () => { sendSignal('decline').catch(() => {}); closeCall(false); });
    $('endCall').addEventListener('click', () => closeCall(true));
    $('toggleMic').addEventListener('click', event => {
        const track = localStream?.getAudioTracks()[0];
        if (track) { track.enabled = !track.enabled; event.currentTarget.classList.toggle('off', !track.enabled); }
    });
    $('toggleCamera').addEventListener('click', event => {
        const track = localStream?.getVideoTracks()[0];
        if (track) { track.enabled = !track.enabled; event.currentTarget.classList.toggle('off', !track.enabled); }
    });
    window.addEventListener('beforeunload', () => active && fetch(root.dataset.sendUrl, {
        method: 'POST', keepalive: true,
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf},
        body: JSON.stringify({type: 'hangup', payload: {}}),
    }));
    poll();
    setInterval(poll, 1500);
}
