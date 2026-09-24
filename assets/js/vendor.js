var VendorCDN = {
    lucide: {
        key: 'lucide',
        global: 'lucide',
        url: 'https://cdn.jsdelivr.net/npm/lucide@1.47.0/dist/umd/lucide.min.js',
        integrity: 'sha384-v15JX+vZHMR3T6LQR9N6e6wIKrKOdNTbdj8j+e5irqq3L9gCauJz2vGlUzomWyUp'
    },
    flatpickrJs: {
        key: 'flatpickrJs',
        global: 'flatpickr',
        url: 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js',
        integrity: 'sha384-5JqMv4L/Xa0hfvtF06qboNdhvuYXUku9ZrhZh3bSk8VXF0A/RuSLHpLsSV9Zqhl6'
    },
    flatpickrCss: {
        key: 'flatpickrCss',
        url: 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css',
        integrity: 'sha384-RkASv+6KfBMW9eknReJIJ6b3UnjKOKC5bOUaNgIY778NFbQ8MtWq9Lr/khUgqtTt'
    },
    dayjs: {
        key: 'dayjs',
        global: 'dayjs',
        url: 'https://cdn.jsdelivr.net/npm/dayjs@1.11.23/dayjs.min.js',
        integrity: 'sha384-1Ft4/JTNbt7S/44V0himpcjx+YfQ1a6W6SYBJMr94ba6jwZhWoWoLaZsMmtJILgq'
    },
    apexcharts: {
        key: 'apexcharts',
        global: 'ApexCharts',
        url: 'https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js',
        integrity: 'sha384-S+z6GyrrYmfdZiLuK+I0tCaabgSR/FH/+NIE3Xgu9W9UL0sFgPzGgoXVfwBeviXD'
    },
    html2pdf: {
        key: 'html2pdf',
        global: 'html2pdf',
        url: 'https://cdn.jsdelivr.net/npm/html2pdf.js@0.10.2/dist/html2pdf.bundle.min.js',
        integrity: 'sha384-aBc0BOllaGWrQx51DYt978St/L7B+21jzNGc4N/jnGD0NxGwj8S/ftRgW0AkXIak'
    }
};

var vendorScriptPromises = {};

var vendorStylePromises = {};

function vendorLoadScript(spec) {
    if (spec.global && window[spec.global]) {
        return Promise.resolve(window[spec.global]);
    }
    if (vendorScriptPromises[spec.key]) {
        return vendorScriptPromises[spec.key];
    }

    vendorScriptPromises[spec.key] = new Promise(function (resolve, reject) {
        var script = document.createElement('script');
        script.src = spec.url;
        script.async = true;
        if (spec.integrity) {
            script.integrity = spec.integrity;
        }
        script.crossOrigin = 'anonymous';
        var timeoutId = setTimeout(function () {
            cleanup();
            delete vendorScriptPromises[spec.key];
            reject(new Error('Timed out loading ' + spec.url));
        }, 15000);
        function cleanup() {
            clearTimeout(timeoutId);
            script.onload = null;
            script.onerror = null;
            if (script.parentNode) {
                script.parentNode.removeChild(script);
            }
        }
        script.onload = function () {
            clearTimeout(timeoutId);
            resolve(spec.global ? window[spec.global] : true);
        };
        script.onerror = function () {
            cleanup();
            delete vendorScriptPromises[spec.key];
            reject(new Error('Failed to load ' + spec.url));
        };
        document.head.appendChild(script);
    });

    return vendorScriptPromises[spec.key];
}

function vendorLoadStyle(spec) {
    if (vendorStylePromises[spec.key]) {
        return vendorStylePromises[spec.key];
    }

    vendorStylePromises[spec.key] = new Promise(function (resolve, reject) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = spec.url;
        if (spec.integrity) {
            link.integrity = spec.integrity;
        }
        link.crossOrigin = 'anonymous';
        var timeoutId = setTimeout(function () {
            if (link.parentNode) {
                link.parentNode.removeChild(link);
            }
            delete vendorStylePromises[spec.key];
            reject(new Error('Timed out loading ' + spec.url));
        }, 15000);
        link.onload = function () { clearTimeout(timeoutId); resolve(); };
        link.onerror = function () {
            clearTimeout(timeoutId);
            if (link.parentNode) {
                link.parentNode.removeChild(link);
            }
            delete vendorStylePromises[spec.key];
            reject(new Error('Failed to load ' + spec.url));
        };
        document.head.appendChild(link);
    });

    return vendorStylePromises[spec.key];
}

var Icons = {
    
    ensure: function () {
        return vendorLoadScript(VendorCDN.lucide);
    },

    
    render: function (root) {
        return Icons.ensure().then(function (lucide) {
            if (!lucide || typeof lucide.createIcons !== 'function') {
                return false;
            }
            lucide.createIcons({ root: root || document });
            return true;
        }).catch(function () {
            return false;
        });
    }
};

function vendorPad2(value) {
    return (value < 10 ? '0' : '') + value;
}

var DateUtils = {
    
    ensure: function () {
        return vendorLoadScript(VendorCDN.dayjs);
    },

    
    today: function () {
        if (typeof window.dayjs === 'function') {
            return window.dayjs().format('YYYY-MM-DD');
        }
        var now = new Date();
        return now.getFullYear() + '-' + vendorPad2(now.getMonth() + 1) + '-' + vendorPad2(now.getDate());
    },

    
    toDateOnly: function (date) {
        if (!date) return '';
        if (typeof window.dayjs === 'function') {
            return window.dayjs(date).format('YYYY-MM-DD');
        }
        var value = (date instanceof Date) ? date : new Date(date);
        if (isNaN(value.getTime())) return '';
        return value.getFullYear() + '-' + vendorPad2(value.getMonth() + 1) + '-' + vendorPad2(value.getDate());
    },

    
    shift: function (dateStr, days) {
        if (typeof window.dayjs === 'function') {
            return window.dayjs(dateStr).add(days, 'day').format('YYYY-MM-DD');
        }
        var base = dateStr ? new Date(dateStr + 'T00:00:00') : new Date();
        if (isNaN(base.getTime())) return '';
        base.setDate(base.getDate() + days);
        return DateUtils.toDateOnly(base);
    }
};

var DatePickers = {
    
    ensure: function () {
        return Promise.all([
            vendorLoadStyle(VendorCDN.flatpickrCss),
            vendorLoadScript(VendorCDN.flatpickrJs)
        ]).then(function () {
            return window.flatpickr;
        });
    },

    
    collect: function (root) {
        var scope = root || document;
        if (scope.nodeType === 1 && scope.matches && scope.matches('input[type="date"]')) {
            return [scope];
        }
        if (scope.querySelectorAll) {
            return Array.prototype.slice.call(scope.querySelectorAll('input[type="date"]'));
        }
        return [];
    },

    
    init: function (root, options) {
        var inputs = DatePickers.collect(root);
        if (!inputs.length) {
            return Promise.resolve(0);
        }
        return DatePickers.ensure().then(function (flatpickr) {
            if (typeof flatpickr !== 'function') {
                return 0;
            }
            var bound = 0;
            inputs.forEach(function (input) {
                if (input.dataset.epFpBound === 'true') return;
                if (input.disabled || input.readOnly) return;

                var config = {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    disableMobile: false,
                    defaultDate: input.value || undefined
                };
                if (options && typeof options === 'object') {
                    for (var key in options) {
                        if (Object.prototype.hasOwnProperty.call(options, key)) {
                            config[key] = options[key];
                        }
                    }
                }

                try {
                    flatpickr(input, config);
                    input.dataset.epFpBound = 'true';
                    bound++;
                } catch (e) {

                }
            });
            return bound;
        }).catch(function () {
            return 0;
        });
    },

    
    sync: function (input, value) {
        if (!input) return;
        var hasValue = arguments.length > 1;
        var instance = input._flatpickr;
        if (instance && typeof instance.setDate === 'function') {
            instance.setDate(hasValue ? (value || null) : (input.value || null), false);
            return;
        }
        if (hasValue) {
            input.value = value || '';
        }
    },

    
    destroy: function (input) {
        if (input && input._flatpickr && typeof input._flatpickr.destroy === 'function') {
            input._flatpickr.destroy();
            if (input.dataset) delete input.dataset.epFpBound;
        }
    }
};

window.ExpensePro = window.ExpensePro || {};
window.ExpensePro.utils = window.ExpensePro.utils || {};

window.ExpensePro.vendor = {
    cdn: VendorCDN,
    loadScript: vendorLoadScript,
    loadStyle: vendorLoadStyle
};
window.ExpensePro.utils.icons = Icons;
window.ExpensePro.utils.date = DateUtils;
window.ExpensePro.utils.datePickers = DatePickers;

document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('[data-lucide]')) {
        Icons.render(document);
    }
});
