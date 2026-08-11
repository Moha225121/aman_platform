const chatLinks = [...document.querySelectorAll('[data-chat-booking]')];

if (chatLinks.length) {
    const originalTitle = document.title;
    let previousTotal = chatLinks.reduce((total, link) => {
        const badge = link.querySelector('.chat-unread-badge');
        return total + Number(badge?.textContent || 0);
    }, 0);
    let latestMessageId = null;
    let initialized = false;

    const toast = document.createElement('button');
    toast.type = 'button';
    toast.className = 'chat-notification-toast';
    toast.hidden = true;
    document.body.append(toast);

    const notificationPanel = document.createElement('section');
    notificationPanel.className = 'chat-notification-panel';
    notificationPanel.hidden = true;
    notificationPanel.setAttribute('aria-live', 'polite');
    const dashboardAnchor = document.querySelector('.dashboard-content, .counselor-main > header');
    if (dashboardAnchor) dashboardAnchor.insertAdjacentElement('afterbegin', notificationPanel);

    function updateNotificationPanel(data) {
        if (!dashboardAnchor) return;
        const unreadLinks = chatLinks.filter(link => Number(data.bookings?.[link.dataset.chatBooking] || 0) > 0);
        notificationPanel.hidden = unreadLinks.length === 0;
        notificationPanel.replaceChildren();
        if (!unreadLinks.length) return;

        const heading = document.createElement('div');
        const title = document.createElement('b');
        const summary = document.createElement('span');
        title.textContent = 'رسائل جديدة';
        summary.textContent = `لديك ${data.total} رسالة غير مقروءة`;
        heading.append(title, summary);
        notificationPanel.append(heading);

        unreadLinks.forEach(link => {
            const count = Number(data.bookings[link.dataset.chatBooking]);
            const item = document.createElement('a');
            const label = document.createElement('span');
            const badge = document.createElement('b');
            item.href = link.href;
            label.textContent = link.textContent.replace(/\d+/g, '').trim();
            badge.textContent = String(count);
            item.append(label, badge);
            notificationPanel.append(item);
        });
    }

    function updateBadges(data) {
        chatLinks.forEach(link => {
            const count = Number(data.bookings?.[link.dataset.chatBooking] || 0);
            const badge = link.querySelector('.chat-unread-badge');
            if (!badge) return;
            badge.textContent = count;
            badge.hidden = count === 0;
        });
        document.title = data.total ? `(${data.total}) ${originalTitle}` : originalTitle;
        updateNotificationPanel(data);
    }

    function showNotification(data) {
        if (!data.latest) return;
        const link = chatLinks.find(item => item.dataset.chatBooking === String(data.latest.booking_id));
        const title = document.createElement('b');
        const preview = document.createElement('span');
        title.textContent = 'رسالة دردشة جديدة';
        preview.textContent = data.latest.preview;
        toast.replaceChildren(title, preview);
        toast.onclick = () => { if (link) window.location.href = link.href; };
        toast.hidden = false;
        requestAnimationFrame(() => toast.classList.add('show'));
        window.setTimeout(() => { toast.classList.remove('show'); window.setTimeout(() => toast.hidden = true, 250); }, 6000);
    }

    async function checkChatNotifications() {
        try {
            const response = await fetch('/chat/notifications', { headers: { Accept: 'application/json' } });
            if (!response.ok) return;
            const data = await response.json();
            updateBadges(data);
            if (initialized && data.total > previousTotal && data.latest?.id !== latestMessageId) showNotification(data);
            previousTotal = data.total;
            latestMessageId = data.latest?.id ?? latestMessageId;
            initialized = true;
        } catch (_) {}
    }

    checkChatNotifications();
    window.setInterval(checkChatNotifications, 10000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) checkChatNotifications(); });
}
