(function () {
    'use strict';

    function isAbort(error) {
        return !!error && (error.aborted === true || error.stale === true ||
            (error && error.name === 'AbortError'));
    }

    function statusOf(error) {
        if (!error || typeof error !== 'object') return 0;
        return Number(error._status || error.status || 0);
    }

    function friendlyMessage(error, fallback) {
        if (!error) return fallback || 'Something went wrong. Please try again.';
        if (error.aborted && !error.stale) return 'Request cancelled.';
        if (error.stale) return 'Request cancelled.';
        var status = statusOf(error);
        if (error._network) {
            return 'Could not reach the server. Check your connection and try again.';
        }
        if (status === 401) {
            return 'Your session has expired. Redirecting to login…';
        }
        if (status === 403) {
            return 'Security check failed. Please refresh the page and try again.';
        }
        if (status === 404) {
            return (error && error.message) || 'The requested item no longer exists.';
        }
        if (status === 409) {
            return (error && error.message) || 'This conflicts with existing data.';
        }
        if (status === 422) {
            return (error && error.message) || 'Please correct the highlighted fields.';
        }
        if (status === 429) {
            return (error && error.message) || 'Too many requests. Please wait a moment and retry.';
        }
        if (status >= 500) {
            return 'Something went wrong on the server. Please try again.';
        }
        if (error instanceof TypeError) {
            return 'An application error occurred. Please reload the page.';
        }
        if (typeof error.message === 'string' && error.message) {
            return error.message;
        }
        return fallback || 'Something went wrong. Please try again.';
    }

    function applyFieldErrors(error, options) {
        options = options || {};
        var errors = error && error.errors;
        if (!errors || typeof errors !== 'object') return false;
        var focused = false;
        Object.keys(errors).forEach(function (key) {
            var fieldId = (options.fieldMap && options.fieldMap[key]) || key;
            
            if (typeof window.showFieldError === 'function') {
                window.showFieldError(fieldId, String(errors[key]));
            }
            if (!focused && options.focusFirst !== false) {
                var el = document.getElementById(fieldId);
                if (el && typeof el.focus === 'function') {
                    try { el.focus({ preventScroll: false }); } catch (e) { try { el.focus(); } catch (ignored) {} }
                    focused = true;
                }
            }
        });
        return true;
    }

    function handleApiError(error, options) {
        options = options || {};
        if (isAbort(error)) {
            
            return { handled: true, kind: error.stale ? 'stale' : 'abort', message: 'Request cancelled.' };
        }
        var status = statusOf(error);
        if (status === 401 && options.redirectLogin !== false) {
            if (typeof window.showToast === 'function') {
                window.showToast('Your session has expired. Redirecting to login…', 'warning');
            }
            var base = (window.ExpensePro && window.ExpensePro.config && window.ExpensePro.config.baseUrl) || (typeof window.BASE_URL === 'string' ? window.BASE_URL : '/ExpensePro/');
            setTimeout(function () {
                window.location.href = base + '?page=login';
            }, 900);
            return { handled: true, kind: 'auth', message: friendlyMessage(error) };
        }
        var mapped = applyFieldErrors(error, options);
        var message = friendlyMessage(error, options.fallback);
        if (!mapped && typeof window.showToast === 'function' && options.toast !== false) {
            window.showToast(message, status >= 500 ? 'error' : (status === 0 ? 'error' : 'error'));
        }
        return { handled: true, kind: 'error', status: status, message: message, fieldMapped: mapped };
    }

    window.ExpensePro = window.ExpensePro || {};
    window.ExpensePro.errors = {
        isAbort: isAbort,
        statusOf: statusOf,
        friendlyMessage: friendlyMessage,
        applyFieldErrors: applyFieldErrors,
        handleApiError: handleApiError
    };
})();
