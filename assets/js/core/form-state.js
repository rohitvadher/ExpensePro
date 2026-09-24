(function () {
    'use strict';


    function setButtonLoading(button, loading, options) {
        options = options || {};
        var el = typeof button === 'string' ? document.getElementById(button) : button;
        if (!el) return false;
        if (loading) {
            if (el.dataset.busy === '1') return false;
            el.dataset.busy = '1';
            if (el.dataset.label === undefined) {
                el.dataset.label = el.innerHTML;
            }
            el.disabled = true;
            el.setAttribute('aria-disabled', 'true');
            if (options.loadingLabel) {
                el.setAttribute('aria-label', options.loadingLabel);
            }
            if (typeof window.Loader !== 'undefined' && typeof window.Loader.button === 'function') {
                window.Loader.button(el, true);
            } else {
                el.innerHTML = '<span aria-hidden="true">Loading…</span>';
            }
            return true;
        }
        el.dataset.busy = '0';
        if (el.dataset.label !== undefined) {
            el.innerHTML = el.dataset.label;
            delete el.dataset.label;
        }
        el.disabled = !!options.keepDisabled;
        el.setAttribute('aria-disabled', el.disabled ? 'true' : 'false');
        return true;
    }

    function guardSubmit(button, fn, options) {
        var el = typeof button === 'string' ? document.getElementById(button) : button;
        if (!el) {
            return Promise.resolve(fn());
        }
        if (el.dataset.busy === '1' || el.disabled) {
            return Promise.resolve(null);
        }
        setButtonLoading(el, true, options);
        var result;
        try {
            result = fn();
        } catch (error) {
            setButtonLoading(el, false, options);
            throw error;
        }
        return Promise.resolve(result).then(function (value) {
            setButtonLoading(el, false, options);
            return value;
        }, function (error) {
            setButtonLoading(el, false, options);
            throw error;
        });
    }

    function bindValidity(form, submitBtn, isValidFn) {
        if (!form || !submitBtn) return function () {};
        var btn = typeof submitBtn === 'string' ? document.getElementById(submitBtn) : submitBtn;
        if (!btn) return function () {};
        function refresh() {
            var valid = true;
            try {
                valid = isValidFn();
            } catch (e) {
                valid = false;
            }
            if (btn.dataset.busy !== '1') {
                btn.disabled = !valid;
                btn.setAttribute('aria-disabled', valid ? 'false' : 'true');
            }
        }
        form.addEventListener('input', refresh);
        form.addEventListener('change', refresh);
        refresh();
        return refresh;
    }

    window.ExpensePro = window.ExpensePro || {};
    window.ExpensePro.formState = {
        setButtonLoading: setButtonLoading,
        guardSubmit: guardSubmit,
        bindValidity: bindValidity
    };
})();
