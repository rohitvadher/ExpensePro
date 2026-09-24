var budgetsData = [];
var budgetsAbort = null;
var budgetsSeq = 0;

document.addEventListener('DOMContentLoaded', function () {
    loadBudgetCategoryOptions();
    loadBudgets();
});

function loadBudgetCategoryOptions() {
    ajaxRequest('GET', BASE_URL + 'api/categories.php')
        .then(function (response) {
            if (response.success && response.data) {
                var select = document.getElementById('budget-category');
                if (!select) return;
                var html = '<option value="">All Expenses</option>';
                response.data.forEach(function (cat) {
                    if (cat.type !== 'expense') return;
                    html += '<option value="' + escapeHtml(String(cat.id)) + '">' + escapeHtml(cat.name) + '</option>';
                });
                select.innerHTML = html;
            }
        })
        .catch(function () {
        });
}

function loadBudgets() {
    var container = document.getElementById('budgets-list');
    if (!container) return;
    if (budgetsAbort) {
        try { budgetsAbort.abort(); } catch (e) {}
    }
    budgetsAbort = ('AbortController' in window) ? new AbortController() : null;
    var signal = budgetsAbort ? budgetsAbort.signal : undefined;
    var mySeq = ++budgetsSeq;
    container.innerHTML = '<div class="col-span-full text-center py-12 text-slate-400 text-sm"><img src="' + BASE_URL + 'assets/icons/SVG/3-dots-fade.svg" alt="Loading" width="36" height="36" class="mx-auto mb-2">Loading budgets...</div>';

    ajaxRequest('GET', BASE_URL + 'api/budgets.php', null, { signal: signal })
        .then(function (response) {
            if (mySeq !== budgetsSeq) return;
            if (response.success && response.data) {
                budgetsData = response.data;
                renderBudgets(response.data);
                renderSummary(response.data);
            } else {
                container.innerHTML = '<div class="col-span-full text-center py-12 text-slate-400 text-sm">Failed to load budgets.</div>';
            }
        })
        .catch(function (error) {
            if (error && (error.aborted || error.stale)) return;
            if (mySeq !== budgetsSeq) return;
            container.innerHTML = '<div class="col-span-full text-center py-12 text-rose-400 text-sm">Failed to load budgets.</div>';
            var detail = (window.ExpensePro && window.ExpensePro.errors)
                ? window.ExpensePro.errors.handleApiError(error, { toast: false })
                : { message: (error && error.message) || 'Failed to load budgets.' };
            showToast(detail.message || 'Failed to load budgets.', 'error');
        });
}

function renderSummary(budgets) {
    var totalBudgeted = 0;
    var totalSpent = 0;
    var periods = {};

    budgets.forEach(function (b) {
        totalBudgeted += b.amount;
        totalSpent += b.spent;
        if (b.period) {
            periods[b.period] = true;
        }
    });

    var scopeEl = document.getElementById('summary-scope');
    if (scopeEl) {
        var names = Object.keys(periods);
        if (names.length === 0) {
            scopeEl.textContent = 'No budgets yet';
        } else if (names.length === 1) {
            scopeEl.textContent = 'Combined ' + names[0] + ' budgets';
        } else {
            scopeEl.textContent = 'Combined ' + names.join(' + ') + ' budgets';
        }
    }

    var remaining = totalBudgeted - totalSpent;
    var status = 'On Track';
    var statusColor = 'text-emerald-600';
    if (remaining < 0) {
        status = 'Over Budget';
        statusColor = 'text-rose-600';
    } else if (remaining < totalBudgeted * 0.2) {
        status = 'Caution';
        statusColor = 'text-amber-600';
    }

    document.getElementById('summary-budgeted').textContent = '\u20B9' + totalBudgeted.toLocaleString('en-IN', { minimumFractionDigits: 0 });
    document.getElementById('summary-spent').textContent = '\u20B9' + totalSpent.toLocaleString('en-IN', { minimumFractionDigits: 0 });
    document.getElementById('summary-remaining').textContent = (remaining < 0 ? '-\u20B9' : '\u20B9') + Math.abs(remaining).toLocaleString('en-IN', { minimumFractionDigits: 0 });
    var statusEl = document.getElementById('summary-status');
    statusEl.textContent = status;
    statusEl.className = 'text-2xl font-bold ' + statusColor;
}

function renderBudgets(budgets) {
    var container = document.getElementById('budgets-list');

    if (!budgets || budgets.length === 0) {
        container.innerHTML = '<div class="col-span-full text-center py-12"><svg class="w-16 h-16 mx-auto mb-3 text-slate-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.8"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"/></svg><p class="text-slate-500 text-sm font-medium mb-1">No budgets yet</p><p class="text-slate-400 text-xs">Create your first budget to track your spending limits</p></div>';
        return;
    }

    var html = '';
    budgets.forEach(function (budget) {
        var pct = budget.percentage;
        var barColor = 'bg-emerald-500';
        var textColor = 'text-emerald-600';
        if (pct >= 100) {
            barColor = 'bg-rose-500';
            textColor = 'text-rose-600';
        } else if (pct >= 80) {
            barColor = 'bg-amber-500';
            textColor = 'text-amber-600';
        }

        var categoryName = budget.category_name || 'All Expenses';
        var categoryColor = safeCssColor(budget.category_color);
        var categoryIcon = budget.category_icon || 'tag';

        html +=
            '<div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 card-hover">' +
            '<div class="flex items-start justify-between mb-3">' +
            '<div class="flex items-center gap-3">' +
            '<div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold" style="background: ' + categoryColor + '20; color: ' + categoryColor + '">' +
            categoryName.charAt(0).toUpperCase() +
            '</div>' +
            '<div>' +
            '<h3 class="text-sm font-semibold text-slate-900">' + escapeHtml(categoryName) + '</h3>' +
            '<p class="text-xs text-slate-400 capitalize">' + escapeHtml(budget.period) + '</p>' +
            '</div>' +
            '</div>' +
            '<div class="flex items-center gap-1">' +
            '<button type="button" data-budget-action="edit" data-budget-id="' + budget.id + '" class="p-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all" title="Edit" aria-label="Edit budget">' +
            '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>' +
            '</button>' +
            '<button type="button" data-budget-action="delete" data-budget-id="' + budget.id + '" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all" title="Delete" aria-label="Delete budget">' +
            '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>' +
            '</button>' +
            '</div>' +
            '</div>' +

            '<div class="flex items-center justify-between mb-1.5">' +
            '<span class="text-xs text-slate-400">' + formatCurrency(budget.spent) + ' spent</span>' +
            '<span class="text-xs font-semibold ' + textColor + '">' + formatCurrency(budget.amount) + '</span>' +
            '</div>' +

            '<div class="h-2.5 bg-slate-100 rounded-full overflow-hidden mb-2">' +
            '<div class="h-full rounded-full ' + barColor + ' transition-all duration-500" style="width: ' + Math.min(pct, 100) + '%"></div>' +
            '</div>' +

            '<div class="flex items-center justify-between">' +
            '<span class="text-xs font-medium ' + textColor + '">' + pct + '% used</span>' +
            '<span class="text-xs ' + (budget.remaining > 0 ? 'text-emerald-600' : 'text-rose-600') + ' font-medium">' +
            (budget.remaining > 0 ? formatCurrency(budget.remaining) + ' left' : 'Exceeded by ' + formatCurrency(Math.abs(budget.amount - budget.spent))) +
            '</span>' +
            '</div>' +
            '</div>';
    });

    container.innerHTML = html;

    container.querySelectorAll('[data-budget-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = parseInt(btn.dataset.budgetId, 10) || 0;
            if (btn.dataset.budgetAction === 'edit') {
                openEditBudgetModal(id);
            } else if (btn.dataset.budgetAction === 'delete') {
                deleteBudget(id);
            }
        });
    });
}

function openCreateBudgetModal() {
    document.getElementById('budget-modal-title').textContent = 'Add Budget';
    document.getElementById('budget-submit-btn').textContent = 'Add Budget';
    document.getElementById('budget-id').value = '';
    document.getElementById('budget-form').reset();
    document.getElementById('budget-form-error').classList.add('hidden');
    document.getElementById('budget-modal').classList.remove('hidden');
}

function openEditBudgetModal(id) {
    var budget = budgetsData.find(function (b) { return b.id === parseInt(id); });
    if (!budget) return;

    document.getElementById('budget-modal-title').textContent = 'Edit Budget';
    document.getElementById('budget-submit-btn').textContent = 'Update Budget';
    document.getElementById('budget-id').value = budget.id;
    document.getElementById('budget-category').value = budget.category_id || '';
    document.getElementById('budget-amount').value = budget.amount;
    document.getElementById('budget-period').value = budget.period;
    document.getElementById('budget-form-error').classList.add('hidden');
    document.getElementById('budget-modal').classList.remove('hidden');
}

function closeBudgetModal() {
    document.getElementById('budget-modal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('budget-form').addEventListener('submit', function (e) {
        e.preventDefault();

        var id = document.getElementById('budget-id').value;
        var categoryId = document.getElementById('budget-category').value || null;
        var amount = document.getElementById('budget-amount').value;
        var period = document.getElementById('budget-period').value;
        var errorEl = document.getElementById('budget-form-error');
        var btn = document.getElementById('budget-submit-btn');

        errorEl.classList.add('hidden');

        var method = id ? 'PUT' : 'POST';
        var payload = {
            category_id: categoryId,
            amount: amount,
            period: period,
            _csrf_token: document.querySelector('input[name="_csrf_token"]').value
        };
        if (id) payload.id = parseInt(id);

        showButtonLoading('budget-submit-btn');

        ajaxRequest(method, BASE_URL + 'api/budgets.php', payload)
            .then(function (response) {
                hideButtonLoading('budget-submit-btn');
                closeBudgetModal();
                showToast(response.message || (id ? 'Budget updated.' : 'Budget created.'), 'success');
                loadBudgets();
            })
            .catch(function (error) {
                hideButtonLoading('budget-submit-btn');
                if (error.errors) {
                    var msgs = Object.values(error.errors).map(function (message) { return escapeHtml(String(message)); }).join('<br>');
                    errorEl.innerHTML = msgs;
                    errorEl.classList.remove('hidden');
                } else {
                    showToast(error.message || 'Failed to save budget.', 'error');
                }
            });
    });
});

function deleteBudget(id) {
    if (!id) return;
    confirmDialog({
        title: 'Delete Budget',
        message: 'Are you sure you want to delete this budget? This action cannot be undone.',
        confirmLabel: 'Delete'
    }).then(function (confirmed) {
        if (!confirmed) return;
        ajaxRequest('DELETE', BASE_URL + 'api/budgets.php?id=' + id, { _csrf_token: document.querySelector('input[name="_csrf_token"]').value })
            .then(function (response) {
                showToast(response.message || 'Budget deleted.', 'success');
                loadBudgets();
            })
            .catch(function (error) {
                showToast(error.message || 'Failed to delete budget.', 'error');
            });
    });
}
