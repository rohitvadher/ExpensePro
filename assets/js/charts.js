var ApexLoader = {
    ensure: function () {
        if (typeof ApexCharts !== 'undefined') {
            return Promise.resolve(true);
        }
        
        if (window.ExpensePro && ExpensePro.vendor && ExpensePro.vendor.loadScript && typeof VendorCDN !== 'undefined') {
            return ExpensePro.vendor.loadScript(VendorCDN.apexcharts).then(function () {
                return typeof ApexCharts !== 'undefined';
            }).catch(function () { return false; });
        }
        return Promise.resolve(false);
    }
};

function whenApexReady(callback) {
    ApexLoader.ensure().then(function (ok) {
        if (ok) {
            try { callback(); } catch (e) {}
        } else if (typeof showToast === 'function') {
            showToast('Charts library failed to load. Please check your connection.', 'error');
        }
    });
}

var expenseProCharts = {};

function destroyChartInstance(elementId) {
    if (expenseProCharts[elementId]) {
        try {
            expenseProCharts[elementId].destroy();
        } catch (e) {}
        delete expenseProCharts[elementId];
    }
}

function mountChart(elementId, options) {
    var el = document.getElementById(elementId);
    if (!el) return;

    destroyChartInstance(elementId);
    whenApexReady(function () {

        var target = document.getElementById(elementId);
        if (!target) return;
        destroyChartInstance(elementId);
        var chart = new ApexCharts(target, options);
        expenseProCharts[elementId] = chart;
        chart.render();
    });
}

var CHART_COLORS = {
    income: '#10B981',
    expense: '#EF4444',
    balance: '#6366F1'
};

function formatCurrencyRaw(value) {
    if (typeof value !== 'number') value = parseFloat(value) || 0;
    return '\u20B9' + value.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function createLineChart(elementId, data) {
    if (!data) return;

    var options = {
        chart: {
            type: 'line',
            height: 300,
            fontFamily: 'Inter, system-ui, sans-serif',
            toolbar: { show: false },
            animations: {
                enabled: true,
                speed: 1000,
                animateGradually: { enabled: true, delay: 100 },
                dynamicAnimation: { enabled: true, speed: 350 }
            },
            background: 'transparent',
            dropShadow: {
                enabled: true,
                top: 0,
                left: 0,
                blur: 4,
                color: CHART_COLORS.balance,
                opacity: 0.15
            }
        },
        colors: [CHART_COLORS.balance],
        series: [{
            name: 'Net Balance',
            data: data.balance
        }],
        xaxis: {
            categories: data.labels,
            labels: { style: { fontSize: '12px', fontWeight: 500 } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { fontSize: '12px' },
                formatter: function (val) {
                    return val >= 1000 ? (val / 1000).toFixed(0) + 'K' : val.toFixed(0);
                }
            }
        },
        grid: {
            strokeDashArray: 4,
            xaxis: { lines: { show: false } }
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        markers: {
            size: 5,
            colors: ['#FFFFFF'],
            strokeColors: CHART_COLORS.balance,
            strokeWidth: 2,
            hover: { size: 7 }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.25,
                opacityTo: 0,
                stops: [0, 90, 100]
            }
        },
        dataLabels: { enabled: false },
        tooltip: {
            y: {
                formatter: function (val) {
                    var prefix = val < 0 ? '-\u20B9' : '\u20B9';
                    return prefix + Math.abs(val).toLocaleString('en-IN', { minimumFractionDigits: 0 });
                }
            }
        },
        states: {
            hover: { filter: { type: 'lighten', value: 0.1 } }
        }
    };

    mountChart(elementId, options);
}

function createMultiLineChart(elementId, data) {
    if (!data) return;

    var options = {
        chart: {
            type: 'line',
            height: 300,
            fontFamily: 'Inter, system-ui, sans-serif',
            toolbar: { show: false },
            animations: {
                enabled: true,
                speed: 1000,
                animateGradually: { enabled: true, delay: 100 },
                dynamicAnimation: { enabled: true, speed: 350 }
            },
            background: 'transparent'
        },
        colors: [CHART_COLORS.income, CHART_COLORS.expense],
        series: [
            { name: 'Income', data: data.income },
            { name: 'Expense', data: data.expense }
        ],
        xaxis: {
            categories: data.labels,
            labels: { style: { fontSize: '12px', fontWeight: 500 } },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: { fontSize: '12px' },
                formatter: function (val) {
                    return val >= 1000 ? (val / 1000).toFixed(0) + 'K' : val.toFixed(0);
                }
            }
        },
        grid: {
            strokeDashArray: 4,
            xaxis: { lines: { show: false } }
        },
        stroke: {
            curve: 'smooth',
            width: 2.5
        },
        markers: {
            size: 4,
            colors: ['#FFFFFF'],
            strokeColors: [CHART_COLORS.income, CHART_COLORS.expense],
            strokeWidth: 2,
            hover: { size: 6 }
        },
        dataLabels: { enabled: false },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            markers: { radius: 6, width: 10, height: 10 }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return formatCurrencyRaw(val);
                }
            }
        },
        states: {
            hover: { filter: { type: 'lighten', value: 0.1 } }
        }
    };

    mountChart(elementId, options);
}

window.addEventListener('pagehide', function () {
    Object.keys(expenseProCharts).forEach(function (key) {
        destroyChartInstance(key);
    });
});
