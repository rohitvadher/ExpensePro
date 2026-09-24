(function () {
    'use strict';

    function readMeta(name) {
        var meta = document.querySelector('meta[name="' + name + '"]');
        return meta ? (meta.getAttribute('content') || '') : '';
    }

    var config = {
        get baseUrl() {
            return typeof window.BASE_URL === 'string' && window.BASE_URL ? window.BASE_URL : '/ExpensePro/';
        },
        get build() {
            return typeof window.EP_BUILD === 'string' ? window.EP_BUILD : 'dev';
        },
        get csrfToken() {
            if (typeof window.getCsrfToken === 'function') {
                try { return window.getCsrfToken() || ''; } catch (e) {  }
            }
            return readMeta('csrf-token');
        }
    };

    window.ExpensePro = window.ExpensePro || {};
    window.ExpensePro.utils = window.ExpensePro.utils || {};
    window.ExpensePro.config = config;
    window.ExpensePro.utils.appConfig = config;
})();
