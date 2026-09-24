window.ExpensePro = window.ExpensePro || {};
window.ExpensePro.utils = window.ExpensePro.utils || {};

window.addEventListener('error', function (e) {
    
    if (typeof showToast === 'function' && e && e.message) {
        showToast('An application error occurred. Please reload the page.', 'error');
    }
});

window.addEventListener('unhandledrejection', function (e) {
    var reason = e && e.reason;
    
    if (reason && (reason.aborted === true || reason.stale === true || reason.name === 'AbortError')) {
        return;
    }
    if (reason && (reason._network === true || reason._status >= 500)) {
        return;
    }
    if (typeof showToast === 'function') {
        showToast('An unexpected error occurred. Please try again.', 'error');
    }
});

document.addEventListener('DOMContentLoaded', function () {
    initCsrf();
    initToastContainer();
    initModalAccessibility();
    initProfileDropdown();
    initA11yEnhancements();
    initDelegatedActions();
    initNotificationBadge();

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var txModal = document.getElementById('transaction-modal');
            var catModal = document.getElementById('category-modal');
            var catDelModal = document.getElementById('delete-category-modal');
            var budgetModal = document.getElementById('budget-modal');

            if (typeof closeTransactionModal === 'function' && txModal && !txModal.classList.contains('hidden')) closeTransactionModal();
            if (typeof closeCategoryModal === 'function' && catModal && !catModal.classList.contains('hidden')) closeCategoryModal();
            if (typeof closeCategoryDeleteModal === 'function' && catDelModal && !catDelModal.classList.contains('hidden')) closeCategoryDeleteModal();
            if (typeof closeBudgetModal === 'function' && budgetModal && !budgetModal.classList.contains('hidden')) closeBudgetModal();
            var moreSheet = document.getElementById('more-sheet');
            if (typeof closeMoreSheet === 'function' && moreSheet && !moreSheet.classList.contains('hidden')) closeMoreSheet();
            var confirmModal = document.getElementById('ep-confirm-modal');
            if (confirmModal && !confirmModal.classList.contains('hidden')) {
                var cancelBtn = document.getElementById('ep-confirm-cancel');
                if (cancelBtn) cancelBtn.click();
            }
        }
    });

    document.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            openCommandPalette();
        }
    });
});

var csrfToken = '';

function initCsrf() {
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        csrfToken = meta.getAttribute('content');
    }
}

function getCsrfToken() {
    return csrfToken;
}

function getCsrfTokenFromMeta() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}


function refreshCsrfToken(token) {
    if (!token) return;
    csrfToken = token;
    var meta = document.querySelector('meta[name="csrf-token"]');
    if (meta) {
        meta.setAttribute('content', token);
    }
    document.querySelectorAll('input[name="_csrf_token"], input[name="csrf_token"]').forEach(function (input) {
        input.value = token;
    });
}


function initNotificationBadge() {
    try {
        if (typeof apiGet !== 'function' || typeof BASE_URL === 'undefined') return;
        if (!document.getElementById('notification-badge') && !document.getElementById('notification-badge-mobile')) return;
        var controller = ('AbortController' in window) ? new AbortController() : null;
        var timer = setTimeout(function () {
            if (controller) {
                try { controller.abort(); } catch (e) {}
            }
        }, 10000);
        apiGet(BASE_URL + 'api/notifications.php?limit=1&unread_only=1')
        .then(function (res) {
            clearTimeout(timer);
            if (res && res.success && res.data) {
                updateNotificationBadges(res.data.unread_count);
            }
        })
        .catch(function () {
            clearTimeout(timer);
        });
    } catch (e) {}
}


function initDelegatedActions() {
    if (initDelegatedActions._bound) return;
    initDelegatedActions._bound = true;
    document.addEventListener('click', function (e) {
        var target = e.target && e.target.closest ? e.target.closest('[data-ep-action]') : null;
        if (!target || !document.contains(target)) return;
        var action = target.getAttribute('data-ep-action');
        if (!action) return;
        var arg = target.getAttribute('data-ep-arg');
        try {
            if (action === 'print') {
                window.print();
                return;
            }
            if (action === 'reload') {
                window.location.reload();
                return;
            }
            var fn = window[action];
            if (typeof fn === 'function') {
                if (arg !== null && arg !== '') {
                    fn(arg, e);
                } else {
                    fn(e);
                }
            }
        } catch (err) {
            if (typeof showToast === 'function') {
                showToast('An application error occurred. Please try again.', 'error');
            }
        }
    });
}

function ajaxRequest(method, url, data, opts) {
    if (typeof data === 'undefined') data = null;
    opts = opts || {};
    
    if (window.ExpensePro && window.ExpensePro.api && typeof window.ExpensePro.api.apiRequest === 'function') {
        return window.ExpensePro.api.apiRequest(method, url, data, opts);
    }
    var options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken()
        },
        credentials: 'same-origin'
    };

    if (opts.signal) {
        options.signal = opts.signal;
    }

    if (data !== null) {
        options.body = JSON.stringify(data);
    }

    return fetch(url, options)
        .then(function (response) {
            var text = response.text();
            return text.then(function (raw) {
                if (!raw || !raw.trim()) {
                    throw { success: false, message: 'Server returned an empty response.', data: null, errors: null, _status: response.status };
                }
                var json;
                try {
                    json = JSON.parse(raw);
                } catch (e) {
                    throw { success: false, message: 'Invalid server response.', data: null, errors: null, _status: response.status };
                }
                if (!response.ok) {
                    json._status = response.status;
                    throw json;
                }
                return json;
            });
        })
        .catch(function (error) {
            if (error && (error.name === 'AbortError' || error.aborted)) {
                error = { aborted: true, message: 'Request cancelled.' };
            }
            throw error;
        });
}

function apiGet(url) {
    return ajaxRequest('GET', url, null);
}

function apiPost(url, data) {
    return ajaxRequest('POST', url, data || {});
}

function ajaxText(method, url, opts) {
    opts = opts || {};
    var options = {
        method: method,
        headers: { 'X-CSRF-Token': getCsrfToken() },
        credentials: 'same-origin'
    };
    if (opts.signal) options.signal = opts.signal;
    return fetch(url, options).then(function (response) {
        return response.text().then(function (text) {
            if (!response.ok) {
                var message = 'Request failed.';
                try {
                    var json = JSON.parse(text);
                    if (json && json.message) message = json.message;
                } catch (e) {}
                throw { success: false, message: message, data: null, errors: null, _status: response.status };
            }
            return text;
        });
    }).catch(function (error) {
        if (error && (error.name === 'AbortError' || error.aborted)) {
            throw { aborted: true, message: 'Request cancelled.' };
        }
        throw error;
    });
}

function apiUpload(url, formData, opts) {
    opts = opts || {};
    
    if (window.ExpensePro && window.ExpensePro.api && typeof window.ExpensePro.api.request === 'function') {
        return window.ExpensePro.api.request('POST', url, { signal: opts.signal, body: formData });
    }
    var options = {
        method: 'POST',
        headers: { 'X-CSRF-Token': getCsrfToken() },
        credentials: 'same-origin',
        body: formData
    };
    if (opts.signal) options.signal = opts.signal;
    return fetch(url, options).then(function (response) {
        return response.text().then(function (raw) {
            if (!raw || !raw.trim()) {
                if (response.ok) {
                    return { success: true, message: 'OK', data: null, errors: null, _status: response.status };
                }
                throw { success: false, message: 'Server returned an empty response.', data: null, errors: null, _status: response.status };
            }
            var json;
            try {
                json = JSON.parse(raw);
            } catch (e) {
                throw { success: false, message: 'Invalid server response. Please try again.', data: null, errors: null, _status: response.status };
            }
            if (!response.ok) {
                json._status = response.status;
                throw json;
            }
            return json;
        });
    }).catch(function (error) {
        if (error && (error.name === 'AbortError' || error.aborted)) {
            throw { aborted: true, message: 'Request cancelled.' };
        }
        if (error instanceof TypeError) {
            throw { success: false, message: 'Could not reach the server. Check your connection and try again.', data: null, errors: null, _network: true };
        }
        throw error;
    });
}

function debounce(fn, wait) {
    var timer = null;
    return function () {
        var args = arguments;
        var self = this;
        if (timer) clearTimeout(timer);
        timer = setTimeout(function () {
            timer = null;
            fn.apply(self, args);
        }, wait || 300);
    };
}

window.ExpensePro.utils.debounce = debounce;

function initToastContainer() {
    if (!document.getElementById('toast-container')) {
        var container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText =
            'position: fixed; top: 20px; right: 20px; z-index: var(--z-toast); display: flex; ' +
            'flex-direction: column; gap: 10px; max-width: 380px; width: 100%; ' +
            'pointer-events: none;';

        if (window.innerWidth < 768) {
            container.style.left = '50%';
            container.style.transform = 'translateX(-50%)';
            container.style.maxWidth = 'calc(100% - 32px)';
        }

        document.body.appendChild(container);
    }
}

function showToast(message, type, duration) {
    if (typeof type !== 'string') type = 'info';
    if (typeof duration !== 'number') duration = 4000;

    var container = document.getElementById('toast-container');
    if (!container) return;

    var configs = {
        success: { bg: '#10B981', icon: 'check-circle' },
        error:   { bg: '#EF4444', icon: 'x-circle' },
        warning: { bg: '#F59E0B', icon: 'exclamation-triangle' },
        info:    { bg: '#3B82F6', icon: 'information-circle' }
    };

    var config = configs[type] || configs.info;

    var toast = document.createElement('div');
    toast.style.cssText =
        'background: white; border-left: 4px solid ' + config.bg + '; ' +
        'border-radius: 10px; padding: 14px 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.12); ' +
        'display: flex; align-items: center; gap: 12px; font-size: 14px; ' +
        'color: #1E293B; pointer-events: auto; transform: translateX(120%); ' +
        'transition: transform 0.3s ease, opacity 0.3s ease; opacity: 0;';

    var iconSpan = document.createElement('span');
    iconSpan.style.cssText =
        'width: 20px; height: 20px; border-radius: 50%; background: ' + config.bg + '; ' +
        'flex-shrink: 0; display: flex; align-items: center; justify-content: center;';

    if (type === 'success') {
        iconSpan.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    } else if (type === 'error') {
        iconSpan.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
    } else {
        iconSpan.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
    }

    var textSpan = document.createElement('span');
    textSpan.style.cssText = 'flex: 1; line-height: 1.4;';
    textSpan.textContent = message;

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.innerHTML = '&times;';
    closeBtn.setAttribute('aria-label', 'Dismiss notification');
    closeBtn.style.cssText =
        'background: none; border: none; font-size: 20px; color: #94A3B8; ' +
        'cursor: pointer; padding: 0 0 0 8px; line-height: 1;';
    closeBtn.addEventListener('click', function () { dismissToast(toast); });

    toast.appendChild(iconSpan);
    toast.appendChild(textSpan);
    toast.appendChild(closeBtn);
    container.appendChild(toast);

    requestAnimationFrame(function () {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    });

    if (duration > 0) {
        setTimeout(function () {
            dismissToast(toast);
        }, duration);
    }
}

function dismissToast(toast) {
    if (!toast || toast._dismissing) return;
    toast._dismissing = true;

    toast.style.transform = 'translateX(120%)';
    toast.style.opacity = '0';

    setTimeout(function () {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 300);
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}

window.ExpensePro.utils.escapeHtml = escapeHtml;

function escapeAttr(text) {
    if (!text) return '';
    return text.replace(/&/g, '&amp;')
               .replace(/'/g, '&#39;')
               .replace(/"/g, '&quot;')
               .replace(/</g, '&lt;')
               .replace(/>/g, '&gt;');
}

window.ExpensePro.utils.escapeAttr = escapeAttr;

function safeCssColor(color, fallback) {
    fallback = fallback || '#6366F1';
    if (typeof color === 'string' && /^#[0-9A-Fa-f]{6}$/.test(color)) {
        return color;
    }
    return fallback;
}

window.ExpensePro.utils.safeCssColor = safeCssColor;

function formatDate(dateStr) {
    if (!dateStr) return '';
    var parts = dateStr.split('T')[0].split('-');
    if (parts.length < 3) {
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        parts = [String(d.getFullYear()), String(d.getMonth() + 1), String(d.getDate())];
    }
    var year = parseInt(parts[0], 10);
    var month = parseInt(parts[1], 10) - 1;
    var day = parseInt(parts[2], 10);
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                  'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return day + ' ' + months[month] + ' ' + year;
}

window.ExpensePro.utils.formatDate = formatDate;

function formatCurrency(amount) {
    if (window.ExpensePro && window.ExpensePro.finance) {
        return window.ExpensePro.finance.formatINR(amount);
    }
    if (amount === null || amount === undefined || isNaN(amount)) return '\u20B90';
    var num = parseFloat(amount);
    var isNeg = num < 0;
    num = Math.abs(num);
    var parts = num.toFixed(2).split('.');
    var intPart = parts[0];
    var decPart = parts[1];
    var lastThree = intPart.substring(intPart.length - 3);
    var otherNumbers = intPart.substring(0, intPart.length - 3);
    if (otherNumbers !== '') {
        lastThree = ',' + lastThree;
    }
    var formatted = otherNumbers.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + lastThree;
    return (isNeg ? '-\u20B9' : '\u20B9') + formatted + '.' + decPart;
}

window.ExpensePro.utils.formatCurrency = formatCurrency;

function isValidEmail(email) {
    if (window.ExpensePro && window.ExpensePro.validation) {
        return window.ExpensePro.validation.isValidEmail(email);
    }
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function isValidPassword(password) {
    if (window.ExpensePro && window.ExpensePro.validation) {
        return window.ExpensePro.validation.isValidPassword(password);
    }
    if (password.length < 8) return false;
    if (!/[A-Za-z]/.test(password)) return false;
    if (!/[0-9]/.test(password)) return false;
    return true;
}

function showFieldError(fieldId, message) {
    var field = document.getElementById(fieldId);
    if (!field) return;

    field.classList.add('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500/20');

    var errorEl = field.parentNode.querySelector('.field-error');
    if (!errorEl) {
        errorEl = document.createElement('p');
        errorEl.className = 'field-error text-rose-500 text-xs mt-1.5';
        field.parentNode.appendChild(errorEl);
    }
    errorEl.textContent = message;
}

function clearFieldError(fieldId) {
    var field = document.getElementById(fieldId);
    if (!field) return;

    field.classList.remove('border-rose-500', 'focus:border-rose-500', 'focus:ring-rose-500/20');
    field.classList.add('border-slate-300');

    var errorEl = field.parentNode.querySelector('.field-error');
    if (errorEl) {
        errorEl.textContent = '';
    }
}

function initA11yEnhancements() {
    document.querySelectorAll('main').forEach(function (m, i) {
        if (!m.id) m.id = i === 0 ? 'ep-main' : 'ep-main-' + i;
        if (!m.hasAttribute('tabindex')) m.setAttribute('tabindex', '-1');
    });

    document.querySelectorAll('a svg, button svg').forEach(function (svg) {
        if (!svg.hasAttribute('aria-hidden') && !svg.hasAttribute('aria-label')) {
            svg.setAttribute('aria-hidden', 'true');
        }
    });
}

function initProfileDropdown() {
    var btn = document.getElementById('profile-menu-btn');
    var menu = document.getElementById('profile-dropdown');
    if (!btn || !menu) return;

    btn.setAttribute('aria-haspopup', 'menu');
    btn.setAttribute('aria-expanded', 'false');

    function close() {
        if (menu.classList.contains('hidden')) return;
        menu.classList.add('hidden');
        btn.setAttribute('aria-expanded', 'false');
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var willOpen = menu.classList.contains('hidden');
        menu.classList.toggle('hidden');
        btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        if (willOpen) {
            var first = menu.querySelector('a, button');
            if (first) {
                try { first.focus({ preventScroll: true }); } catch (err) {}
            }
        }
    });

    document.addEventListener('click', function (e) {
        if (!menu.classList.contains('hidden') && !menu.contains(e.target)) {
            close();
            try { btn.focus({ preventScroll: true }); } catch (err) {}
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !menu.classList.contains('hidden')) {
            close();
            try { btn.focus({ preventScroll: true }); } catch (err) {}
        }
    });
}

function showButtonLoading(buttonId) {
    if (typeof Loader !== 'undefined' && typeof Loader.button === 'function') {
        Loader.button(buttonId, true);
    }
}

function hideButtonLoading(buttonId) {
    if (typeof Loader !== 'undefined' && typeof Loader.button === 'function') {
        Loader.button(buttonId, false);
    }
}

function haptic(pattern) {
    try {
        if (typeof navigator !== 'undefined' && typeof navigator.vibrate === 'function') {
            navigator.vibrate(pattern || 10);
        }
    } catch (e) {

    }
}

var ScrollLock = {
    _count: 0,

    lock: function () {
        this._count++;
        this._apply();
    },

    unlock: function () {
        if (this._count > 0) this._count--;
        this._apply();
    },

    reset: function () {
        this._count = 0;
        this._apply();
    },

    _apply: function () {
        var locked = this._count > 0;
        document.documentElement.style.overflow = locked ? 'hidden' : '';
        document.body.style.overflow = locked ? 'hidden' : '';
    }
};

function lockScroll() { ScrollLock.lock(); }

function unlockScroll() { ScrollLock.unlock(); }

window.addEventListener('pagehide', function () { ScrollLock.reset(); });
window.addEventListener('pageshow', function () {

    if (document.querySelectorAll('[id$="-modal"]:not(.hidden), #more-sheet:not(.hidden)').length === 0) {
        ScrollLock.reset();
    }
});

function initModalAccessibility() {
    function dialogs() {
        return Array.prototype.slice.call(document.querySelectorAll('[id$="-modal"], #more-sheet, #command-palette'));
    }

    function focusables(root) {
        return Array.prototype.slice.call(root.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter(function (el) {
            return el.offsetParent !== null;
        });
    }

    function enhance(dialog) {
        if (!dialog || dialog._epA11y) return;
        dialog._epA11y = true;
        dialog.setAttribute('role', 'dialog');
        dialog.setAttribute('aria-modal', 'true');
        if (!dialog.hasAttribute('aria-label') && !dialog.hasAttribute('aria-labelledby')) {
            var heading = dialog.querySelector('h1, h2, h3');
            if (heading) {
                if (!heading.id) {
                    heading.id = dialog.id + '-title';
                }
                dialog.setAttribute('aria-labelledby', heading.id);
            } else {
                dialog.setAttribute('aria-label', dialog.id.replace(/-/g, ' '));
            }
        }
        if (!dialog.hasAttribute('tabindex')) {
            dialog.setAttribute('tabindex', '-1');
        }
    }

    function sync(dialog) {

        if (!document.contains(dialog)) {
            if (dialog._epOpen) {
                dialog._epOpen = false;
                unlockScroll();
            }
            return;
        }
        var open = !dialog.classList.contains('hidden');
        if (open && !dialog._epOpen) {
            dialog._epOpen = true;
            lockScroll();
            if (!dialog._epOpener || !document.contains(dialog._epOpener)) {
                dialog._epOpener = document.activeElement;
            }
            var targets = focusables(dialog);
            (targets[0] || dialog).focus({ preventScroll: true });
        } else if (!open && dialog._epOpen) {
            dialog._epOpen = false;
            unlockScroll();
            var opener = dialog._epOpener;
            dialog._epOpener = null;
            if (opener && document.contains(opener) && typeof opener.focus === 'function') {
                opener.focus({ preventScroll: true });
            }
        }
    }

    dialogs().forEach(function (dialog) {
        enhance(dialog);
        sync(dialog);
    });

    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function () {
            dialogs().forEach(function (dialog) {
                enhance(dialog);
                sync(dialog);
            });
        });
        observer.observe(document.body, { attributes: true, subtree: true, attributeFilter: ['class'], childList: true });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Tab') return;
        var openDialogs = dialogs().filter(function (dialog) {
            return !dialog.classList.contains('hidden');
        });
        if (openDialogs.length === 0) return;
        var dialog = openDialogs[openDialogs.length - 1];
        var targets = focusables(dialog);
        if (targets.length === 0) {
            e.preventDefault();
            dialog.focus();
            return;
        }
        var first = targets[0];
        var last = targets[targets.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault();
            first.focus();
        }
    });
}

var EP_EMPTY_ICON = '<svg class="ep-state-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>';
var EP_ERROR_ICON = '<svg class="ep-state-icon ep-state-icon-error" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.8"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01"/></svg>';

function emptyState(o) {
    o = o || {};
    return '<div class="ep-state">' + EP_EMPTY_ICON +
        '<p class="ep-state-title">' + escapeHtml(o.title || 'Nothing here yet') + '</p>' +
        '<p class="ep-state-message">' + escapeHtml(o.message || '') + '</p></div>';
}

function errorState(o) {
    o = o || {};
    return '<div class="ep-state">' + EP_ERROR_ICON +
        '<p class="ep-state-title">Something went wrong</p>' +
        '<p class="ep-state-message">' + escapeHtml(o.message || 'Please try again.') + '</p>' +
        '<button type="button" data-ep-retry="1" class="ep-state-retry">Retry</button></div>';
}

function bindRetry(container, fn) {
    if (!container) return;
    var btn = container.querySelector('[data-ep-retry]');
    if (btn) btn.addEventListener('click', fn);
}

window.ExpensePro.utils.emptyState = emptyState;
window.ExpensePro.utils.errorState = errorState;

function updateNotificationBadges(count) {
    ['notification-badge', 'notification-badge-mobile'].forEach(function (id) {
        var badge = document.getElementById(id);
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    });
}

function confirmDialog(o) {
    o = o || {};
    return new Promise(function (resolve) {
        var modal = document.getElementById('ep-confirm-modal');
        if (!modal) { resolve(window.confirm(o.message || 'Are you sure?')); return; }
        var titleEl = document.getElementById('ep-confirm-title');
        var msgEl = document.getElementById('ep-confirm-message');
        var okBtn = document.getElementById('ep-confirm-ok');
        var cancelBtn = document.getElementById('ep-confirm-cancel');
        var backdrop = modal.querySelector('[data-ep-confirm-backdrop]');
        if (titleEl) titleEl.textContent = o.title || 'Are you sure?';
        if (msgEl) msgEl.textContent = o.message || 'This action cannot be undone.';
        if (okBtn) okBtn.textContent = o.confirmLabel || 'Delete';

        function onOk() { haptic([20, 40, 20]); done(true); }
        function onCancel() { done(false); }
        function onBackdrop() { done(false); }
        function done(value) {
            modal.classList.add('hidden');
            okBtn.removeEventListener('click', onOk);
            cancelBtn.removeEventListener('click', onCancel);
            if (backdrop) backdrop.removeEventListener('click', onBackdrop);
            resolve(value);
        }
        okBtn.addEventListener('click', onOk);
        cancelBtn.addEventListener('click', onCancel);
        if (backdrop) backdrop.addEventListener('click', onBackdrop);
        modal.classList.remove('hidden');
    });
}

function handleLogout() {
    ajaxRequest('POST', BASE_URL + 'api/logout.php', {
        csrf_token: getCsrfToken()
    })
    .then(function () {
        window.location.href = BASE_URL + '?page=login';
    })
    .catch(function (error) {
        var status = (window.ExpensePro && window.ExpensePro.errors && typeof window.ExpensePro.errors.statusOf === 'function')
            ? window.ExpensePro.errors.statusOf(error)
            : 0;
        if (status === 403) {
            
            
            if (typeof showToast === 'function') {
                showToast('Security token expired. Reloading — please log out again.', 'warning');
            }
            setTimeout(function () { window.location.reload(); }, 1200);
            return;
        }
        window.location.href = BASE_URL + '?page=login';
    });
}

function openMoreSheet() {
    try { haptic(10); } catch (e) {}
    var sheet = document.getElementById('more-sheet');
    if (!sheet || !sheet.classList.contains('hidden')) return;
    sheet.classList.remove('hidden');
    if (typeof lockScroll === 'function') {
        lockScroll();
    } else {
        document.body.style.overflow = 'hidden';
    }
}

function closeMoreSheet() {
    var sheet = document.getElementById('more-sheet');
    if (!sheet || sheet.classList.contains('hidden')) return;
    sheet.classList.add('hidden');
    if (typeof unlockScroll === 'function') {
        unlockScroll();
    } else {
        document.body.style.overflow = '';
    }
}

function openCommandPalette() {
    var existing = document.getElementById('command-palette');
    if (existing) {
        existing.remove();
    }

    var overlay = document.createElement('div');
    overlay.id = 'command-palette';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-label', 'Command palette');
    overlay.style.cssText =
        'position: fixed; inset: 0; z-index: var(--z-modal); background: rgba(0,0,0,0.4); ' +
        'backdrop-filter: blur(4px); display: flex; align-items: flex-start; ' +
        'justify-content: center; padding-top: 15vh;';

    var box = document.createElement('div');
    box.style.cssText =
        'background: white; border-radius: 16px; box-shadow: 0 25px 60px rgba(0,0,0,0.2); ' +
        'width: 100%; max-width: 480px; overflow: hidden;';

    var searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Type a command or search...';
    searchInput.setAttribute('aria-label', 'Search commands');
    searchInput.style.cssText =
        'width: 100%; padding: 16px 20px; border: none; border-bottom: 1px solid #e2e8f0; ' +
        'font-size: 15px; outline: none; background: transparent;';
    box.appendChild(searchInput);

    var commands = [
        { label: 'Dashboard', url: BASE_URL + '?page=dashboard' },
        { label: 'Transactions', url: BASE_URL + '?page=transactions' },
        { label: 'Add Transaction', action: function () { window.location.href = BASE_URL + '?page=transactions'; } },
        { label: 'Categories', url: BASE_URL + '?page=categories' },
        { label: 'Analytics', url: BASE_URL + '?page=analytics' },
        { label: 'Budgets', url: BASE_URL + '?page=budgets' },
        { label: 'Import CSV', url: BASE_URL + '?page=import' },
        { label: 'Reports', url: BASE_URL + '?page=reports' },
        { label: 'Notifications', url: BASE_URL + '?page=notifications' },
        { label: 'Profile', url: BASE_URL + '?page=profile' },
        { label: 'Help & Guide', url: BASE_URL + '?page=help' }
    ];

    var list = document.createElement('div');
    list.style.cssText = 'max-height: 320px; overflow-y: auto;';

    function renderCommands(filter) {
        list.innerHTML = '';
        var filtered = commands.filter(function (cmd) {
            if (!filter) return true;
            return cmd.label.toLowerCase().indexOf(filter.toLowerCase()) !== -1;
        });

        if (filtered.length === 0) {
            list.innerHTML = '<div style="padding: 16px 20px; color: #94a3b8; font-size: 14px; text-align: center;">No results found</div>';
            return;
        }

        filtered.forEach(function (cmd) {
            var item = document.createElement('button');
            item.type = 'button';
            item.style.cssText =
                'width: 100%; text-align: left; padding: 12px 20px; font-size: 14px; ' +
                'color: #334155; background: transparent; border: none; cursor: pointer; ' +
                'display: flex; align-items: center; gap: 10px; transition: background 0.15s;';
            item.textContent = cmd.label;
            item.addEventListener('mouseenter', function () { this.style.background = '#f1f5f9'; });
            item.addEventListener('mouseleave', function () { this.style.background = 'transparent'; });
            item.addEventListener('click', function () {
                overlay.remove();
                if (cmd.url) window.location.href = cmd.url;
                if (cmd.action) cmd.action();
            });
            list.appendChild(item);
        });
    }

    renderCommands('');
    box.appendChild(list);
    overlay.appendChild(box);

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) overlay.remove();
    });

    document.body.appendChild(overlay);
    searchInput.focus();

    searchInput.addEventListener('input', function () {
        renderCommands(this.value);
    });

    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            overlay.remove();
        }
        if (e.key === 'Enter') {
            var firstItem = list.querySelector('button');
            if (firstItem) firstItem.click();
        }
    });
}
