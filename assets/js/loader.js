var LoaderDotsSVG =
    '<svg xmlns="http://www.w3.org/2000/svg" width="__SIZE__" height="__SIZE__" viewBox="0 0 24 24" fill="#4f46e5" aria-hidden="true">' +
    '<circle cx="4" cy="12" r="3"><animate fill="freeze" attributeName="opacity" begin="0;epDotsEnd.end-0.25s" dur="0.75s" values="1;.2"/></circle>' +
    '<circle cx="12" cy="12" r="3" opacity=".4"><animate fill="freeze" attributeName="opacity" begin="0.15s" dur="0.75s" values="1;.2"/></circle>' +
    '<circle cx="20" cy="12" r="3" opacity=".3"><animate id="epDotsEnd" fill="freeze" attributeName="opacity" begin="0.3s" dur="0.75s" values="1;.2"/></circle>' +
    '</svg>';

function loaderDots(size) {
    return LoaderDotsSVG.split('__SIZE__').join(size || 40);
}

var Loader = {
    _timers: {},
    _active: 0,

    page: function (key, delay) {
        key = key || 'global';
        this.clear(key);
        var self = this;
        this._timers[key] = setTimeout(function () {
            var el = document.getElementById('ep-page-loader');
            if (!el) {
                el = document.createElement('div');
                el.id = 'ep-page-loader';
                el.innerHTML = '<div class="ep-loader-box">' + loaderDots(56) + '<p>Loading…</p></div>';
                document.body.appendChild(el);

                void el.offsetWidth;
            }
            el.classList.add('show');
            self._active++;
        }, delay || 350);
    },

    done: function (key) {
        key = key || 'global';
        this.clear(key);
        if (this._active > 0) this._active--;
        if (this._active <= 0) {
            this._active = 0;
            var el = document.getElementById('ep-page-loader');
            if (el) {
                el.classList.remove('show');
                setTimeout(function () {
                    var node = document.getElementById('ep-page-loader');
                    if (node && !node.classList.contains('show')) node.remove();
                }, 250);
            }
        }
    },

    clear: function (key) {
        if (this._timers[key]) {
            clearTimeout(this._timers[key]);
            delete this._timers[key];
        }
    },

    inline: function (container, message) {
        var el = typeof container === 'string' ? document.getElementById(container) : container;
        if (!el) return;
        el.textContent = '';
        var box = document.createElement('div');
        box.className = 'ep-inline-loader';
        box.innerHTML = loaderDots(36);
        if (message) {
            var p = document.createElement('p');
            p.textContent = message;
            box.appendChild(p);
        }
        el.appendChild(box);
    },

    button: function (btn, loading) {
        var el = typeof btn === 'string' ? document.getElementById(btn) : btn;
        if (!el) return;
        if (loading) {
            if (el.dataset.busy === '1') return;
            el.dataset.busy = '1';
            el.dataset.label = el.innerHTML;
            el.disabled = true;
            el.innerHTML = '<span class="ep-btn-dots">' + loaderDots(22) + '</span>';
        } else {
            el.dataset.busy = '0';
            if (el.dataset.label) el.innerHTML = el.dataset.label;
            el.disabled = false;
        }
    }
};

function trackRequest(promise, key, delay) {
    Loader.page(key, delay);
    return promise.then(
        function (value) { Loader.done(key); return value; },
        function (error) { Loader.done(key); throw error; }
    );
}
