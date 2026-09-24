var notificationsAbort = null;
var notificationsSeq = 0;

document.addEventListener('DOMContentLoaded', function () {
    loadNotifications();
});

function loadNotifications() {
    var container = document.getElementById('notifications-container');
    if (notificationsAbort) {
        try { notificationsAbort.abort(); } catch (e) {}
    }
    notificationsAbort = ('AbortController' in window) ? new AbortController() : null;
    var mySeq = ++notificationsSeq;
    var fetchFn = (window.ExpensePro && window.ExpensePro.api)
        ? function () { return window.ExpensePro.api.apiRequest('GET', BASE_URL + 'api/notifications.php?limit=50', null, { signal: notificationsAbort ? notificationsAbort.signal : undefined }); }
        : function () { return apiGet(BASE_URL + 'api/notifications.php?limit=50'); };
    fetchFn()
        .then(function (res) {
            if (mySeq !== notificationsSeq) return;
            if (!container) return;
            if (res && res.success && res.data) {
                renderNotifications(res.data.notifications);
                return;
            }
            
            
            container.innerHTML =
                '<div class="text-center py-12 text-rose-400 text-sm">Failed to load notifications.</div>';
        })
        .catch(function (err) {
            if (err && (err.aborted || err.stale)) return;
            if (mySeq !== notificationsSeq) return;
            if (!container) return;
            container.innerHTML =
                '<div class="text-center py-12 text-rose-400 text-sm">Failed to load notifications.</div>';
        });
}

function renderNotifications(items) {
    var container = document.getElementById('notifications-container');
    if (!items || items.length === 0) {
        container.innerHTML =
            '<div class="text-center py-16">' +
            '<div class="w-20 h-20 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">' +
            '<svg class="w-10 h-10 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">' +
            '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>' +
            '</svg></div>' +
            '<h2 class="text-lg font-semibold text-slate-700 mb-1">All caught up!</h2>' +
            '<p class="text-sm text-slate-400">No notifications at the moment. We will alert you when something needs your attention.</p>' +
            '</div>';
        return;
    }

    var typeConfig = {
        over_budget:        { bg: 'bg-rose-50',   text: 'text-rose-500',   dot: 'bg-rose-500',   link: 'text-rose-600',   icon: 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z', label: 'Budget Alert' },
        login:              { bg: 'bg-indigo-50', text: 'text-indigo-500', dot: 'bg-indigo-500', link: 'text-indigo-600', icon: 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', label: 'Login' },
        info:               { bg: 'bg-slate-50',  text: 'text-slate-500',  dot: 'bg-slate-500',  link: 'text-slate-600',  icon: 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z', label: 'Info' },
    };

    var html = '';
    items.forEach(function (n) {
        var cfg = typeConfig[n.type] || typeConfig.info;
        var unreadClass = n.is_read ? 'opacity-70' : '';
        var dotClass = n.is_read ? 'hidden' : cfg.dot;
        var dateStr = new Date(n.created_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
        var safeLink = (typeof n.link === 'string' && n.link.indexOf('?page=') === 0) ? n.link : '';
        var linkHtml = safeLink ? '<a href="' + escapeAttr(BASE_URL + safeLink) + '" class="text-xs ' + cfg.link + ' hover:underline font-medium mt-1 inline-block">View &rarr;</a>' : '';
        html +=
            '<div class="bg-white rounded-xl border border-slate-100 p-4 flex items-start gap-3 ' + unreadClass + ' cursor-pointer transition-all hover:shadow-md" ' +
            'data-notification-id="' + n.id + '" role="button" tabindex="0" aria-label="Mark notification as read: ' + escapeAttr(n.title) + '">' +
            '<div class="w-10 h-10 rounded-xl ' + cfg.bg + ' flex items-center justify-center flex-shrink-0">' +
            '<svg class="w-5 h-5 ' + cfg.text + '" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">' +
            '<path stroke-linecap="round" stroke-linejoin="round" d="' + escapeHtml(cfg.icon) + '"/>' +
            '</svg>' +
            '</div>' +
            '<div class="flex-1 min-w-0">' +
            '<div class="flex items-center justify-between gap-2">' +
            '<p class="text-sm font-semibold text-slate-900 truncate">' + escapeHtml(n.title) + '</p>' +
            '<span class="w-2 h-2 rounded-full flex-shrink-0 ' + dotClass + '"></span>' +
            '</div>' +
            '<p class="text-sm text-slate-500 mt-0.5">' + escapeHtml(n.message) + '</p>' +
            linkHtml +
            '<p class="text-xs text-slate-400 mt-1">' + escapeHtml(dateStr) + '</p>' +
            '</div>' +
            '</div>';
    });
    container.innerHTML = html;

    container.querySelectorAll('[data-notification-id]').forEach(function (card) {
        var activate = function () {
            markRead(parseInt(card.dataset.notificationId, 10) || 0, card);
        };
        card.addEventListener('click', function (e) {
            if (e.target.closest('a')) return;
            activate();
        });
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                activate();
            }
        });
    });
}

function markRead(id, el) {
    apiPost(BASE_URL + 'api/notifications.php', { action: 'mark_read', id: parseInt(id) })
        .then(function (res) {
            if (el) {
                el.classList.add('opacity-70');
                var dot = el.querySelector('.rounded-full.flex-shrink-0');
                if (dot) dot.classList.add('hidden');
            }
            updateBellCount(res.data.unread_count);
        })
        .catch(function () {});
}

function markAllRead() {
    apiPost(BASE_URL + 'api/notifications.php', { action: 'mark_read', ids: 'all' })
        .then(function (res) {
            loadNotifications();
            updateBellCount(0);
            showToast('All notifications marked as read.', 'success');
        })
        .catch(function () {
            showToast('Failed to mark notifications as read.', 'error');
        });
}

function updateBellCount(count) {
    if (typeof updateNotificationBadges === 'function') {
        updateNotificationBadges(count);
    }
}
