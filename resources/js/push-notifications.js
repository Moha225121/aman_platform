const supported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
const decodeKey = value => { const padding = '='.repeat((4 - value.length % 4) % 4); const raw = atob((value + padding).replace(/-/g, '+').replace(/_/g, '/')); return Uint8Array.from([...raw].map(char => char.charCodeAt(0))); };
async function enableNotifications(button) {
    button.disabled = true;
    try {
        if (await Notification.requestPermission() !== 'granted') throw new Error('لم يتم السماح بالإشعارات');
        const registration = await navigator.serviceWorker.register('/call-sw.js');
        const config = await fetch('/push/config', {headers: {Accept: 'application/json'}}).then(response => response.json());
        if (!config.public_key) throw new Error('الإشعارات غير مهيأة على الخادم');
        let subscription = await registration.pushManager.getSubscription();
        subscription ||= await registration.pushManager.subscribe({userVisibleOnly: true, applicationServerKey: decodeKey(config.public_key)});
        const response = await fetch('/push/subscriptions', {method: 'POST', headers: {'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content}, body: JSON.stringify({...subscription.toJSON(), contentEncoding: PushManager.supportedContentEncodings?.[0] || 'aes128gcm'})});
        if (!response.ok) throw new Error('تعذر حفظ اشتراك الإشعارات');
        button.textContent = 'إشعارات المكالمات مفعّلة ✓'; button.classList.add('enabled');
    } catch (error) { button.disabled = false; button.textContent = error.message; setTimeout(() => button.textContent = 'تفعيل إشعارات المكالمات', 4000); }
}
if (supported) {
    navigator.serviceWorker.register('/call-sw.js').catch(() => {});
    const button = document.createElement('button'); button.type = 'button'; button.className = 'push-enable-button';
    button.textContent = Notification.permission === 'granted' ? 'إشعارات المكالمات مفعّلة ✓' : 'تفعيل إشعارات المكالمات';
    if (Notification.permission === 'granted') button.classList.add('enabled');
    button.onclick = () => enableNotifications(button);
    document.querySelector('.booking-chat-context, .dashboard-content, .counselor-main')?.prepend(button);
}
