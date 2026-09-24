(function () {
    'use strict';

    function getCsrf() {
        if (typeof window.getCsrfToken === 'function') {
            try { return window.getCsrfToken() || ''; } catch (e) { return ''; }
        }
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? (meta.getAttribute('content') || '') : '';
    }

    function isAbortError(error) {
        return !!error && (error.name === 'AbortError' || error.aborted === true || error.code === 20);
    }

    function parseJsonBody(raw, status) {
        if (!raw || !raw.trim()) {
            throw { success: false, message: 'Server returned an empty response.', data: null, errors: null, _status: status };
        }
        try {
            return JSON.parse(raw);
        } catch (e) {
            throw { success: false, message: 'Invalid server response. Please try again.', data: null, errors: null, _status: status };
        }
    }

    function coreRequest(method, url, options) {
        options = options || {};
        var fetchOptions = {
            method: method,
            credentials: 'same-origin'
        };
        if (options.signal) {
            fetchOptions.signal = options.signal;
        }
        if (options.body !== undefined) {
            if (options.body instanceof FormData) {
                fetchOptions.body = options.body;
                fetchOptions.headers = { 'X-CSRF-Token': getCsrf() };
            } else {
                fetchOptions.headers = {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': getCsrf()
                };
                fetchOptions.body = options.body === null ? undefined : JSON.stringify(options.body);
            }
        } else if (options.json !== false) {
            fetchOptions.headers = {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrf()
            };
        } else {
            fetchOptions.headers = { 'X-CSRF-Token': getCsrf() };
        }

        return fetch(url, fetchOptions).then(function (response) {
            return response.text().then(function (raw) {
                
                if (!raw || !raw.trim()) {
                    if (response.ok) {
                        return { success: true, message: 'OK', data: null, errors: null, _status: response.status };
                    }
                    throw { success: false, message: 'Server returned an empty response.', data: null, errors: null, _status: response.status };
                }
                var json = parseJsonBody(raw, response.status);
                if (!response.ok) {
                    json._status = response.status;
                    throw json;
                }
                return json;
            });
        }).catch(function (error) {
            if (isAbortError(error)) {
                throw { aborted: true, message: 'Request cancelled.' };
            }
            
            if (error instanceof TypeError) {
                throw { success: false, message: 'Could not reach the server. Check your connection and try again.', data: null, errors: null, _network: true };
            }
            throw error;
        });
    }

    function apiRequest(method, url, data, opts) {
        opts = opts || {};
        if (typeof data === 'undefined') data = null;
        if (method === 'GET' || method === 'HEAD') {
            return coreRequest(method, url, { signal: opts.signal, json: true });
        }
        return coreRequest(method, url, { signal: opts.signal, body: data });
    }

    
    var seqByKey = {};
    function latestOnly(key, executor) {
        seqByKey[key] = (seqByKey[key] || 0) + 1;
        var mySeq = seqByKey[key];
        return executor().then(function (value) {
            if (seqByKey[key] !== mySeq) {
                var stale = { aborted: true, stale: true, message: 'Stale response ignored.' };
                throw stale;
            }
            return value;
        }, function (error) {
            if (seqByKey[key] !== mySeq && !(error && error.aborted)) {
                throw { aborted: true, stale: true, message: 'Stale response ignored.' };
            }
            throw error;
        });
    }

    function makeAborter() {
        var controller = null;
        return {
            next: function () {
                if (controller) {
                    try { controller.abort(); } catch (e) {}
                }
                controller = ('AbortController' in window) ? new AbortController() : null;
                return controller ? controller.signal : undefined;
            },
            signal: function () {
                return controller ? controller.signal : undefined;
            },
            abort: function () {
                if (controller) {
                    try { controller.abort(); } catch (e) {}
                }
            }
        };
    }

    window.ExpensePro = window.ExpensePro || {};
    window.ExpensePro.api = {
        request: coreRequest,
        apiRequest: apiRequest,
        latestOnly: latestOnly,
        makeAborter: makeAborter,
        isAbortError: isAbortError
    };
})();
