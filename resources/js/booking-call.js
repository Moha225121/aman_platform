const root = document.getElementById('bookingCall');

if (root) {
    const $ = id => document.getElementById(id);
    const startButton = $('startCall'), remoteVideo = $('remoteVideo'), localVideo = $('localVideo');
    const waiting = $('callWaiting'), title = $('callTitle'), status = $('callStatus'), answerControls = $('callAnswer');
    let peer = null, localStream = null, lastSignalId = 0, active = false, caller = false, polling = false;
    let pendingCandidates = [];
    const iceServers = (() => {
        try { return JSON.parse(root.dataset.iceServers || '[]'); }
        catch { return [{urls: 'stun:stun.l.google.com:19302'}]; }
    })();

    const sendSignal = async (type, payload = {}) => {
        const response = await fetch(root.dataset.sendUrl, {method: 'POST', headers: {
            Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf,
        }, body: JSON.stringify({type, payload})});
        if (!response.ok) throw new Error('signal');
    };

    const openMedia = async () => {
        if (localStream) return localStream;
        if (!window.isSecureContext || !navigator.mediaDevices?.getUserMedia) {
            const error = new Error('secure-context'); error.name = 'SecurityError'; throw error;
        }
        localStream = await navigator.mediaDevices.getUserMedia({
            audio: {echoCancellation: true, noiseSuppression: true},
            video: {facingMode: 'user', width: {ideal: 1280}, height: {ideal: 720}},
        });
        localVideo.srcObject = localStream;
        await localVideo.play().catch(() => {});
        return localStream;
    };

    const showFailure = (error = new Error('connection')) => {
        console.error('Encrypted call failed:', error);
        active = false; caller = false; peer?.close(); peer = null;
        localStream?.getTracks().forEach(track => track.stop()); localStream = null;
        localVideo.srcObject = null; remoteVideo.srcObject = null; pendingCandidates = [];
        startButton.disabled = false; root.hidden = false; waiting.hidden = false; answerControls.hidden = true;
        title.textContent = 'تعذر إكمال المكالمة';
        if (['NotAllowedError', 'PermissionDeniedError'].includes(error?.name))
            status.textContent = 'تم رفض إذن الكاميرا أو الميكروفون. اسمح بهما من إعدادات الموقع ثم أعد المحاولة.';
        else if (['NotFoundError', 'DevicesNotFoundError'].includes(error?.name))
            status.textContent = 'لم يتم العثور على كاميرا أو ميكروفون متاح في هذا الجهاز.';
        else if (['NotReadableError', 'TrackStartError'].includes(error?.name))
            status.textContent = 'الكاميرا أو الميكروفون مستخدمان في تطبيق آخر. أغلقه ثم أعد المحاولة.';
        else if (error?.name === 'SecurityError')
            status.textContent = 'تشغيل الكاميرا والميكروفون يتطلب فتح الموقع عبر HTTPS آمن.';
        else if (!iceServers.some(server => String(server.urls).startsWith('turn')))
            status.textContent = 'تعذر إنشاء الاتصال بين الشبكتين. يجب إعداد خادم TURN للمكالمات الخارجية.';
        else status.textContent = 'تعذر الاتصال بالطرف الآخر. تحقق من الشبكة ثم حاول مرة أخرى.';
    };

    const createPeer = async () => {
        if (peer) return peer;
        peer = new RTCPeerConnection({iceServers});
        (await openMedia()).getTracks().forEach(track => peer.addTrack(track, localStream));
        peer.ontrack = event => { remoteVideo.srcObject = event.streams[0]; remoteVideo.play().catch(() => {}); waiting.hidden = true; };
        peer.onicecandidate = event => event.candidate && sendSignal('ice', {candidate: event.candidate.toJSON()}).catch(showFailure);
        peer.onconnectionstatechange = () => {
            if (peer?.connectionState === 'connected') { status.textContent = 'متصل — الوسائط مشفرة من طرف إلى طرف'; waiting.hidden = true; }
            if (peer?.connectionState === 'failed') showFailure(new Error('connection'));
        };
        return peer;
    };
    const flushCandidates = async () => {
        if (!peer?.remoteDescription) return;
        for (const candidate of pendingCandidates.splice(0)) await peer.addIceCandidate(candidate);
    };
    const closeCall = (notify = false) => {
        if (notify && active) sendSignal('hangup').catch(() => {});
        active = false; caller = false; peer?.close(); peer = null;
        localStream?.getTracks().forEach(track => track.stop()); localStream = null;
        localVideo.srcObject = null; remoteVideo.srcObject = null; pendingCandidates = [];
        root.hidden = true; waiting.hidden = false; answerControls.hidden = true; startButton.disabled = false;
        navigator.serviceWorker?.controller?.postMessage({type: 'close-call-notification', bookingId: root.dataset.bookingId});
    };
    const beginCall = async () => {
        try {
            active = true; caller = true; startButton.disabled = true; root.hidden = false; waiting.hidden = false;
            title.textContent = `جاري الاتصال بـ ${root.dataset.peerName}`; status.textContent = 'بانتظار قبول المكالمة…'; answerControls.hidden = true;
            await openMedia(); await sendSignal('invite');
        } catch (error) { showFailure(error); }
    };
    const handleSignal = async signal => {
        if (signal.type === 'invite' && !active) {
            active = true; caller = false; root.hidden = false; waiting.hidden = false;
            title.textContent = `مكالمة فيديو من ${root.dataset.peerName}`; status.textContent = 'اتصال مباشر ومشفر'; answerControls.hidden = false; return;
        }
        if (!active) return;
        if (signal.type === 'accept' && caller) {
            const connection = await createPeer(), offer = await connection.createOffer();
            await connection.setLocalDescription(offer); await sendSignal('offer', {description: connection.localDescription.toJSON()});
        } else if (signal.type === 'offer' && !caller) {
            const connection = await createPeer(); await connection.setRemoteDescription(signal.payload.description); await flushCandidates();
            const answer = await connection.createAnswer(); await connection.setLocalDescription(answer);
            await sendSignal('answer', {description: connection.localDescription.toJSON()});
        } else if (signal.type === 'answer' && caller && peer) {
            await peer.setRemoteDescription(signal.payload.description); await flushCandidates();
        } else if (signal.type === 'ice' && signal.payload.candidate) {
            if (peer?.remoteDescription) await peer.addIceCandidate(signal.payload.candidate); else pendingCandidates.push(signal.payload.candidate);
        } else if (['decline', 'hangup'].includes(signal.type)) closeCall(false);
    };
    const poll = async () => {
        if (polling) return; polling = true;
        try {
            const response = await fetch(`${root.dataset.signalsUrl}?after=${lastSignalId}`, {headers: {Accept: 'application/json'}});
            if (!response.ok) return;
            const {signals} = await response.json();
            for (const signal of signals) { lastSignalId = Math.max(lastSignalId, signal.id); try { await handleSignal(signal); } catch (error) { showFailure(error); } }
        } finally { polling = false; }
    };

    startButton.addEventListener('click', beginCall);
    $('acceptCall').addEventListener('click', async () => {
        try {
            answerControls.hidden = true; title.textContent = 'جاري بدء المكالمة…'; status.textContent = 'يتم إنشاء اتصال مباشر ومشفر…';
            await openMedia(); await sendSignal('accept');
            navigator.serviceWorker?.controller?.postMessage({type: 'close-call-notification', bookingId: root.dataset.bookingId});
        } catch (error) { showFailure(error); }
    });
    $('declineCall').addEventListener('click', () => { sendSignal('decline').catch(() => {}); closeCall(false); });
    $('endCall').addEventListener('click', () => closeCall(true));
    const toggleTrack = (event, kind, onTitle, offTitle) => {
        const track = kind === 'audio' ? localStream?.getAudioTracks()[0] : localStream?.getVideoTracks()[0];
        if (!track) return; track.enabled = !track.enabled; event.currentTarget.classList.toggle('off', !track.enabled);
        event.currentTarget.setAttribute('aria-pressed', String(!track.enabled)); event.currentTarget.title = track.enabled ? onTitle : offTitle;
    };
    $('toggleMic').addEventListener('click', event => toggleTrack(event, 'audio', 'كتم الميكروفون', 'تشغيل الميكروفون'));
    $('toggleCamera').addEventListener('click', event => toggleTrack(event, 'video', 'إيقاف الكاميرا', 'تشغيل الكاميرا'));
    window.addEventListener('beforeunload', () => active && fetch(root.dataset.sendUrl, {method: 'POST', keepalive: true,
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf}, body: JSON.stringify({type: 'hangup', payload: {}})}));
    poll(); setInterval(poll, 1500);
}
