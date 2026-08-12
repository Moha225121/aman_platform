self.addEventListener('push', event => {
    const data = event.data?.json() || {};
    event.waitUntil(self.registration.showNotification(data.title || 'مكالمة واردة', {
        body: data.body || 'لديك مكالمة واردة', icon: '/images/aman/logo.png', badge: '/images/aman/logo.png',
        tag: `aman-call-${data.booking_id}`, renotify: true, requireInteraction: true,
        vibrate: [250, 120, 250, 120, 350], data: {url: data.url},
        actions: [{action: 'answer', title: 'فتح وقبول'}, {action: 'dismiss', title: 'تجاهل'}],
    }));
});
self.addEventListener('notificationclick', event => {
    event.notification.close();
    if (event.action === 'dismiss') return;
    const url = new URL(event.notification.data.url, self.location.origin).href;
    event.waitUntil(clients.matchAll({type: 'window', includeUncontrolled: true}).then(windows => {
        const existing = windows.find(client => client.url.startsWith(new URL(url).origin));
        if (existing) return existing.navigate(url).then(client => client.focus());
        return clients.openWindow(url);
    }));
});
self.addEventListener('message', event => {
    if (event.data?.type === 'close-call-notification') self.registration.getNotifications({tag: `aman-call-${event.data.bookingId}`}).then(items => items.forEach(item => item.close()));
});
