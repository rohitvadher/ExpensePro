var analyticsAbort = null;
var analyticsLoadedOnce = false;

document.addEventListener('DOMContentLoaded', function () {
    loadAnalytics(false);
});

function refreshAnalytics() {
    haptic(10);
    loadAnalytics(true);
}

function loadAnalytics(isRefresh) {
    if (analyticsAbort) {
        try { analyticsAbort.abort(); } catch (e) {}
    }
    analyticsAbort = (typeof AbortController !== 'undefined') ? new AbortController() : null;

    if (!isRefresh) {
        var loadingTimeout = setTimeout(function () {
            showToast('Loading is taking longer than expected. Please check your connection.', 'warning');
        }, 15000);
    } else {
        if (typeof destroyChartInstance === 'function') {
            destroyChartInstance('line-chart');
            destroyChartInstance('multi-line-chart');
        }
        Loader.inline('line-chart', 'Refreshing\u2026');
        Loader.inline('multi-line-chart', '');
        var loadingTimeout = null;
    }

    ajaxRequest('GET', BASE_URL + 'api/dashboard.php', null,
        analyticsAbort ? { signal: analyticsAbort.signal } : {})
        .then(function (response) {
            if (loadingTimeout) clearTimeout(loadingTimeout);
            if (response.success && response.data) {
                renderAnalyticsSummary(response.data.summary);
                renderCategoryBreakdownList(response.data.category_breakdown);

                var lineChartEl = document.getElementById('line-chart');
                if (response.data.balance_trend && response.data.balance_trend.labels && response.data.balance_trend.labels.length > 0) {
                    createLineChart('line-chart', response.data.balance_trend);
                } else if (lineChartEl) {
                    lineChartEl.innerHTML = '<div class="flex items-center justify-center h-[300px] text-slate-400 text-sm">No trend data available yet.</div>';
                }

                var multiLineEl = document.getElementById('multi-line-chart');
                if (response.data.balance_trend && response.data.balance_trend.labels && response.data.balance_trend.labels.length > 0) {
                    createMultiLineChart('multi-line-chart', response.data.balance_trend);
                } else if (multiLineEl) {
                    multiLineEl.innerHTML = '<div class="flex items-center justify-center h-[300px] text-slate-400 text-sm">No trend data available yet.</div>';
                }

                analyticsLoadedOnce = true;
            } else {
                
                showToast((response && response.message) || 'Failed to load analytics data.', 'error');
                ['line-chart', 'multi-line-chart'].forEach(function (id) {
                    var el = document.getElementById(id);
                    if (el) el.innerHTML = '<div class="flex items-center justify-center h-[300px] text-slate-400 text-sm">Failed to load chart data.</div>';
                });
                var breakdownEmpty = document.getElementById('category-breakdown-list');
                if (breakdownEmpty) breakdownEmpty.innerHTML = '<div class="text-center py-8 text-slate-400 text-sm">Failed to load breakdown.</div>';
            }
        })
        .catch(function (error) {
            if (loadingTimeout) clearTimeout(loadingTimeout);
            if (error && error.aborted) return;
            showToast(error.message || 'Failed to load analytics.', 'error');
            ['line-chart', 'multi-line-chart'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.innerHTML = '<div class="flex items-center justify-center h-[300px] text-slate-400 text-sm">Failed to load chart data.</div>';
            });
            var breakdown = document.getElementById('category-breakdown-list');
            if (breakdown) breakdown.innerHTML = '<div class="text-center py-8 text-slate-400 text-sm">Failed to load breakdown.</div>';
        });
}

function renderAnalyticsSummary(summary) {
    var income = summary.total_income || 0;
    var expense = summary.total_expense || 0;
    var balance = summary.balance || 0;
    var maxVal = Math.max(income, expense, balance, 1);

    var incomeEl = document.getElementById('stat-income');
    if (incomeEl) incomeEl.textContent = '\u20B9' + income.toLocaleString('en-IN', { minimumFractionDigits: 0 });

    var expenseEl = document.getElementById('stat-expense');
    if (expenseEl) expenseEl.textContent = '\u20B9' + expense.toLocaleString('en-IN', { minimumFractionDigits: 0 });

    var balanceEl = document.getElementById('stat-balance');
    if (balanceEl) {
        balanceEl.textContent = (balance < 0 ? '-\u20B9' : '\u20B9') + Math.abs(balance).toLocaleString('en-IN', { minimumFractionDigits: 0 });
        balanceEl.className = 'text-2xl font-bold ' + (balance >= 0 ? 'text-emerald-600' : 'text-rose-600');
    }

    var incomeBar = document.getElementById('income-bar');
    if (incomeBar) incomeBar.style.width = (income / maxVal * 100) + '%';

    var expenseBar = document.getElementById('expense-bar');
    if (expenseBar) expenseBar.style.width = (expense / maxVal * 100) + '%';

    var balanceBar = document.getElementById('balance-bar');
    if (balanceBar) balanceBar.style.width = (Math.abs(balance) / maxVal * 100) + '%';
}

function renderCategoryBreakdownList(categories) {
    var container = document.getElementById('category-breakdown-list');
    if (!container) return;

    if (!categories || categories.length === 0) {
        container.innerHTML = '<div class="text-center py-8"><svg class="w-12 h-12 mx-auto mb-2 text-slate-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg><p class="text-slate-500 text-sm">No expense data yet.</p></div>';
        return;
    }

    var html = '';
    categories.forEach(function (cat) {
        html +=
            '<div class="flex items-center justify-between">' +
            '<div class="flex items-center gap-3">' +
            '<div class="w-3 h-3 rounded-full" style="background-color: ' + safeCssColor(cat.color) + '"></div>' +
            '<span class="text-sm font-medium text-slate-700">' + escapeHtml(cat.name) + '</span>' +
            '</div>' +
            '<div class="text-right">' +
            '<span class="text-sm font-semibold text-slate-900">\u20B9' + cat.total.toLocaleString('en-IN', { minimumFractionDigits: 0 }) + '</span>' +
            '<span class="text-xs text-slate-500 ml-2">' + escapeHtml(String(cat.percentage)) + '%</span>' +
            '</div>' +
            '</div>' +
            '<hr class="border-slate-100">';
    });

    container.innerHTML = html;
}
