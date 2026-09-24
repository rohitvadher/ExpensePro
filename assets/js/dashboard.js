var Dashboard = {
    _abort: null,
    _booted: false,
    _slowTimer: null,

    boot: function () {

        if (this._booted) return;
        if (!document.getElementById('stat-income')) return;
        this._booted = true;
        this.load();
    },

    load: function () {
        var self = this;
        if (this._abort) {
            try { this._abort.abort(); } catch (e) {}
        }
        this._abort = (typeof AbortController !== 'undefined') ? new AbortController() : null;

        this._setLoading();
        if (this._slowTimer) clearTimeout(this._slowTimer);
        this._slowTimer = setTimeout(function () {
            showToast('Loading is taking longer than expected. Please check your connection.', 'warning');
        }, 15000);

        ajaxRequest('GET', BASE_URL + 'api/dashboard.php', null,
            this._abort ? { signal: this._abort.signal } : {})
            .then(function (response) {
                self._clearSlowTimer();
                if (response && response.success && response.data) {
                    self.render(response.data);
                } else {
                    self.error('Failed to load dashboard data.');
                }
            })
            .catch(function (err) {
                self._clearSlowTimer();
                if (err && err.aborted) return;
                self.error((err && err.message) || 'Failed to load dashboard.');
            });
    },

    _clearSlowTimer: function () {
        if (this._slowTimer) {
            clearTimeout(this._slowTimer);
            this._slowTimer = null;
        }
    },

    _setLoading: function () {
        var recentEl = document.getElementById('recent-transactions');
        if (recentEl && typeof Loader !== 'undefined') {
            Loader.inline(recentEl, 'Loading transactions…');
        }
    },

    render: function (data) {
        this._renderSummary(data.summary || {});
        this._renderRecent(data.recent_transactions || []);
        this._renderBudget(data.budget_status);
    },

    error: function (message) {
        showToast(message, 'error');
        this._renderSummary(null);
        var recentEl = document.getElementById('recent-transactions');
        if (recentEl) {
            recentEl.innerHTML = errorState({ message: message });
            bindRetry(recentEl, function () { Dashboard.load(); });
        }
        var budgetWidget = document.getElementById('budget-progress-widget');
        if (budgetWidget) budgetWidget.classList.add('hidden');
    },

    _renderSummary: function (summary) {
        var income = summary ? (summary.total_income || 0) : 0;
        var expense = summary ? (summary.total_expense || 0) : 0;
        var balance = summary ? (summary.balance || 0) : 0;
        var count = summary ? (summary.transaction_count || 0) : 0;
        var failed = !summary;

        setCounter('stat-income', income, '₹', '', 0);
        setCounter('stat-expense', expense, '₹', '', 0);

        var balanceEl = document.getElementById('stat-balance');
        if (balanceEl) {
            if (failed) {
                balanceEl.textContent = '---';
            } else {
                var prefix = balance < 0 ? '-₹' : '₹';
                balanceEl.textContent = prefix + Math.abs(balance).toLocaleString('en-IN', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }
            balanceEl.className = 'counter text-2xl font-bold ' +
                (failed ? 'text-slate-400' : (balance >= 0 ? 'text-emerald-600' : 'text-rose-600'));
        }
        setCounter('stat-count', count, '', '', 0);
        if (failed) {
            document.querySelectorAll('.stat-card .counter').forEach(function (el) {
                if (el.id !== 'stat-balance') el.textContent = '---';
            });
        }
    },

    _renderRecent: function (transactions) {
        var container = document.getElementById('recent-transactions');
        if (!container) return;

        if (!transactions || transactions.length === 0) {
            container.innerHTML = emptyState({
                title: 'No transactions yet',
                message: 'Add your first transaction to get started.'
            });
            return;
        }

        var html = '';
        transactions.forEach(function (txn) {
            var isIncome = txn.type === 'income';
            var sign = isIncome ? '+' : '-';
            var colorClass = isIncome ? 'text-emerald-600' : 'text-rose-600';
            var bgColor = isIncome ? 'bg-emerald-100' : 'bg-rose-100';
            var iconColor = isIncome ? 'text-emerald-600' : 'text-rose-600';

            html +=
                '<div class="flex items-center justify-between p-4 bg-white rounded-xl border border-slate-100 hover:border-slate-200 transition-all card-hover">' +
                '<div class="flex items-center gap-3">' +
                '<div class="w-10 h-10 rounded-lg ' + bgColor + ' flex items-center justify-center ' + iconColor + '">' +
                '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">';

            if (isIncome) {
                html += '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75"/>';
            } else {
                html += '<path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75"/>';
            }

            html +=
                '</svg>' +
                '</div>' +
                '<div>' +
                '<p class="text-sm font-medium text-slate-900">' + escapeHtml(txn.category_name || 'Uncategorized') + '</p>' +
                '<p class="text-xs text-slate-500">' + (txn.description ? escapeHtml(txn.description) : 'No description') + ' &middot; ' + formatDate(txn.date) + '</p>' +
                '</div>' +
                '</div>' +
                '<div class="text-right">' +
                '<p class="text-sm font-semibold ' + colorClass + '">' + sign + '₹' + parseFloat(txn.amount).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + '</p>' +
                '</div>' +
                '</div>';
        });

        container.innerHTML = html;
    },

    _renderBudget: function (budgetStatus) {
        var widget = document.getElementById('budget-progress-widget');
        var content = document.getElementById('budget-progress-content');
        if (!widget || !content) return;

        if (!budgetStatus || !budgetStatus.monthly_budget || budgetStatus.monthly_budget <= 0) {
            widget.classList.add('hidden');
            return;
        }

        try {
            var pct = budgetStatus.percentage;
            var isOver = budgetStatus.is_over_budget;
            var barColor = isOver ? 'bg-rose-500' : (pct >= 80 ? 'bg-amber-500' : 'bg-emerald-500');
            var healthText = isOver ? 'Over Budget' : (pct >= 80 ? 'Almost Full' : 'On Track');
            var healthColor = isOver ? 'text-rose-600' : (pct >= 80 ? 'text-amber-600' : 'text-emerald-600');

            content.innerHTML =
                '<div class="flex items-center justify-between mb-3">' +
                '<h2 class="text-sm font-semibold text-slate-900">Monthly Budget</h2>' +
                '<span class="text-xs font-medium ' + healthColor + '">' + healthText + '</span>' +
                '</div>' +
                '<div class="flex items-end justify-between mb-2">' +
                '<span class="text-2xl font-bold text-slate-900">₹' + budgetStatus.spent.toLocaleString('en-IN', { minimumFractionDigits: 0 }) + '</span>' +
                '<span class="text-sm text-slate-500">of ₹' + budgetStatus.monthly_budget.toLocaleString('en-IN', { minimumFractionDigits: 0 }) + '</span>' +
                '</div>' +
                '<div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">' +
                '<div class="h-full ' + barColor + ' rounded-full transition-all duration-500" style="width: ' + Math.min(pct, 100) + '%"></div>' +
                '</div>' +
                '<div class="flex items-center justify-between mt-2">' +
                '<span class="text-xs text-slate-400">' + pct + '% used</span>' +
                '<span class="text-xs text-slate-400">₹' + budgetStatus.remaining.toLocaleString('en-IN', { minimumFractionDigits: 0 }) + ' remaining</span>' +
                '</div>';

            widget.classList.remove('hidden');
        } catch (e) {
            widget.classList.add('hidden');
        }
    }
};

function setCounter(elementId, value, prefix, suffix, decimals) {
    var el = document.getElementById(elementId);
    if (!el) return;

    var formatted = Number(value).toFixed(decimals);
    el.textContent = prefix + formatted.replace(/\B(?=(\d{3})+(?!\d))/g, ',') + suffix;
    el.setAttribute('data-target', value);
}

document.addEventListener('DOMContentLoaded', function () {
    Dashboard.boot();
});
