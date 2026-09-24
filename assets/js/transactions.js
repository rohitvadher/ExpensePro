var transactionState = {
    currentPage: 1,
    filters: {
        type: '',
        search: '',
        date_from: '',
        date_to: '',
        category_id: 0
    },
    editingId: null
};

document.addEventListener('DOMContentLoaded', function () {
    try {
        var urlTypeEl = document.getElementById('txn-url-type-data');
        var urlType = urlTypeEl ? (urlTypeEl.getAttribute('data-url-type') || '') : '';
        if (!urlType && typeof txnUrlType !== 'undefined' && txnUrlType) {
            urlType = txnUrlType;
        }
        if (urlType === 'income' || urlType === 'expense') {
            transactionState.filters.type = urlType;
            var typeSelect = document.getElementById('filter-type');
            if (typeSelect) typeSelect.value = urlType;
        }

        initTransactionForm();
        initTransactionFilters();
        initTransactionDatePickers();
        initTransactionListEvents();
        initFilterSheet();
        updateFilterBadge();
        loadFilterCategories();
        loadTransactions();
        initExportButton();
    } catch (e) {
        var container = document.getElementById('transactions-list');
        if (container) {
            container.innerHTML = errorState({ message: 'Something went wrong loading this page.' });
            bindRetry(container, function () { window.location.reload(); });
        }
    }
});

function loadTransactions(page) {
    if (page) transactionState.currentPage = page;

    if (transactionState.abort) {
        try { transactionState.abort.abort(); } catch (e) {}
    }
    transactionState.abort = (typeof AbortController !== 'undefined') ? new AbortController() : null;

    var params = new URLSearchParams({
        page: transactionState.currentPage,
        limit: 15
    });

    var f = transactionState.filters;
    if (f.type) params.set('type', f.type);
    if (f.search) params.set('search', f.search);
    if (f.date_from) params.set('date_from', f.date_from);
    if (f.date_to) params.set('date_to', f.date_to);
    if (f.category_id > 0) params.set('category_id', f.category_id);

    showSkeleton(true);

    var loadTimer = setTimeout(function () {
        showSkeleton(false);
        var container = document.getElementById('transactions-list');
        if (container) {
            container.innerHTML = errorState({ message: 'Loading is taking longer than expected. Please check your connection.' });
            bindRetry(container, function () { loadTransactions(); });
        }
    }, 15000);

    ajaxRequest('GET', BASE_URL + 'api/transactions.php?' + params.toString(), null,
        transactionState.abort ? { signal: transactionState.abort.signal } : {})
        .then(function (response) {
            clearTimeout(loadTimer);
            showSkeleton(false);
            if (response.success && response.data) {
                renderTransactions(response.data.transactions);
                renderPagination(response.data.pagination);
                updateResultCount(response.data.pagination);
            } else {
                
                updateResultCount(null);
                showToast((response && response.message) || 'Failed to load transactions.', 'error');
                var listEl = document.getElementById('transactions-list');
                if (listEl) {
                    listEl.innerHTML = errorState({ message: (response && response.message) || 'Failed to load transactions.' });
                    bindRetry(listEl, function () { loadTransactions(); });
                }
            }
        })
        .catch(function (error) {
            clearTimeout(loadTimer);
            showSkeleton(false);
            if (error && error.aborted) return;
            showToast(error.message || 'Failed to load transactions.', 'error');
            updateResultCount(null);
            var container = document.getElementById('transactions-list');
            if (container) {
                container.innerHTML = errorState({ message: error.message || 'Failed to load transactions.' });
                bindRetry(container, function () { loadTransactions(); });
            }
        });
}

function renderTransactions(transactions) {
    var container = document.getElementById('transactions-list');
    if (!container) return;

    if (!transactions || transactions.length === 0) {
        var hasFilters = transactionState.filters.type || transactionState.filters.search ||
            transactionState.filters.date_from || transactionState.filters.date_to ||
            transactionState.filters.category_id > 0;
        container.innerHTML = emptyState(hasFilters
            ? { title: 'No transactions match these filters', message: 'Try changing or resetting your filters.' }
            : { title: 'No transactions yet', message: 'Add your first transaction to start tracking your finances.' });
        return;
    }

    var html = '';
    transactions.forEach(function (txn) {
        var isIncome = txn.type === 'income';
        var sign = isIncome ? '+' : '-';
        var colorClass = isIncome ? 'text-emerald-600' : 'text-rose-600';
        var bgColor = isIncome ? 'bg-emerald-50 border-emerald-100' : 'bg-rose-50 border-rose-100';
        var iconColor = isIncome ? 'text-emerald-500' : 'text-rose-500';
        var badgeClass = isIncome ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700';

        html +=
            '<div class="bg-white rounded-xl border border-slate-100 p-4 hover:border-slate-200 hover:shadow-sm transition-all card-hover">' +
            '<div class="flex items-start justify-between">' +
            '<div class="flex items-start gap-3">' +
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
            '<p class="text-xs text-slate-500 mt-0.5">' + (txn.description ? escapeHtml(txn.description) : 'No description') + '</p>' +
            '<div class="flex items-center gap-2 mt-2">' +
            '<span class="text-xs text-slate-500">' + formatDate(txn.date) + '</span>' +
            '<span class="text-xs px-2 py-0.5 rounded-full font-medium ' + badgeClass + '">' + txn.type + '</span>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="text-right flex flex-col items-end">' +
            '<p class="text-base font-bold ' + colorClass + '">' + sign + '\u20B9' + parseFloat(txn.amount).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + '</p>' +
            '<div class="flex gap-1 mt-2">' +
            '<button type="button" data-txn-action="edit" data-txn-id="' + txn.id + '" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit" aria-label="Edit transaction">' +
            '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>' +
            '</button>' +
            '<button type="button" data-txn-action="delete" data-txn-id="' + txn.id + '" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete" aria-label="Delete transaction">' +
            '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>' +
            '</button>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';
    });

    container.innerHTML = html;
}

function renderPagination(pagination) {
    var container = document.getElementById('transactions-pagination');
    if (!container) return;

    if (!pagination || pagination.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }

    var current = pagination.page;
    var total = pagination.total_pages;
    var html = '<div class="flex flex-wrap items-center justify-center gap-2 mt-6 px-2">';

    html += '<button type="button" data-txn-page="' + (current - 1) + '" ' +
        (current <= 1 ? 'disabled class="px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-300 cursor-not-allowed"' :
            'class="px-4 py-2 rounded-lg text-sm font-medium bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors min-w-[44px]"') +
        '>Prev</button>';

    var start = Math.max(1, current - 2);
    var end = Math.min(total, current + 2);

    if (start > 1) {
        html += '<button type="button" data-txn-page="1" class="px-4 py-2 rounded-lg text-sm font-medium bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors min-w-[44px]">1</button>';
        if (start > 2) html += '<span class="px-2 text-slate-300">...</span>';
    }

    for (var i = start; i <= end; i++) {
        var active = i === current;
        html += '<button type="button" data-txn-page="' + i + '" ' +
            (active ? 'class="px-4 py-2 rounded-lg text-sm font-medium bg-indigo-600 text-white min-w-[44px]"' :
                'class="px-4 py-2 rounded-lg text-sm font-medium bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors min-w-[44px]"') +
            '>' + i + '</button>';
    }

    if (end < total) {
        if (end < total - 1) html += '<span class="px-2 text-slate-300">...</span>';
        html += '<button type="button" data-txn-page="' + total + '" class="px-4 py-2 rounded-lg text-sm font-medium bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors min-w-[44px]">' + total + '</button>';
    }

    html += '<button type="button" data-txn-page="' + (current + 1) + '" ' +
        (current >= total ? 'disabled class="px-4 py-2 rounded-lg text-sm font-medium bg-slate-100 text-slate-300 cursor-not-allowed"' :
            'class="px-4 py-2 rounded-lg text-sm font-medium bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors min-w-[44px]"') +
        '>Next</button>';

    html += '</div>';
    container.innerHTML = html;
}

function showSkeleton(show) {
    var container = document.getElementById('transactions-list');
    if (!container) return;

    if (show) {
        container.innerHTML = '';
        for (var i = 0; i < 5; i++) {
            container.innerHTML +=
                '<div class="bg-white rounded-xl border border-slate-100 p-4 skeleton">' +
                '<div class="flex items-start justify-between">' +
                '<div class="flex items-start gap-3">' +
                '<div class="w-10 h-10 rounded-lg bg-slate-200"></div>' +
                '<div>' +
                '<div class="h-4 w-32 bg-slate-200 rounded mb-2"></div>' +
                '<div class="h-3 w-48 bg-slate-100 rounded"></div>' +
                '</div>' +
                '</div>' +
                '<div class="text-right">' +
                '<div class="h-5 w-20 bg-slate-200 rounded mb-2"></div>' +
                '</div>' +
                '</div>' +
                '</div>';
        }
    } else {
        container.innerHTML = '';
    }
}

function initTransactionForm() {
    var typeInputs = document.querySelectorAll('input[name="txn_type"]');
    var form = document.getElementById('transaction-form');
    var modal = document.getElementById('transaction-modal');

    if (!form || !modal) return;

    typeInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            loadCategoryOptions(this.value);
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitTransactionForm();
    });

    
    if (window.ExpensePro && window.ExpensePro.formState &&
        typeof window.ExpensePro.formState.bindValidity === 'function') {
        transactionState.refreshSubmit = window.ExpensePro.formState.bindValidity(
            form, 'submit-btn', isTransactionFormValid
        );
    }
}

function isTransactionFormValid() {
    var typeRadio = document.querySelector('input[name="txn_type"]:checked');
    var catEl = document.getElementById('txn_category_id');
    var amtEl = document.getElementById('txn_amount');
    var dateEl = document.getElementById('txn_date');
    var descEl = document.getElementById('txn_description');
    var data = {
        type: typeRadio ? typeRadio.value : '',
        category_id: catEl ? catEl.value : '',
        amount: amtEl ? amtEl.value : '',
        date: dateEl ? dateEl.value : '',
        description: descEl ? descEl.value : ''
    };
    if (window.ExpensePro && window.ExpensePro.validation) {
        return Object.keys(window.ExpensePro.validation.validateTransaction(data)).length === 0;
    }
    var amount = parseFloat(data.amount);
    return !!(data.type && data.category_id && isFinite(amount) && amount > 0 && data.date);
}

function refreshSubmitState() {
    if (typeof transactionState.refreshSubmit === 'function') {
        try { transactionState.refreshSubmit(); } catch (e) {}
        return;
    }
    
    var btn = document.getElementById('submit-btn');
    if (btn && btn.dataset.busy !== '1') {
        var valid = false;
        try { valid = isTransactionFormValid(); } catch (e) {}
        btn.disabled = !valid;
        btn.setAttribute('aria-disabled', valid ? 'false' : 'true');
    }
}

function initTransactionDatePickers() {
    if (window.ExpensePro && ExpensePro.utils && ExpensePro.utils.datePickers) {
        ExpensePro.utils.datePickers.init(document);
    }

    if (window.ExpensePro && ExpensePro.utils && ExpensePro.utils.date && ExpensePro.utils.date.ensure) {
        ExpensePro.utils.date.ensure().catch(function () {});
    }
}

function openCreateModal() {
    transactionState.editingId = null;
    document.getElementById('modal-title').textContent = 'Add Transaction';
    document.getElementById('transaction-form').reset();
    document.getElementById('form-error').classList.add('hidden');
    document.getElementById('submit-btn').textContent = 'Add Transaction';

    var now = new Date();
    var today = (window.ExpensePro && ExpensePro.utils && ExpensePro.utils.date)
        ? ExpensePro.utils.date.today()
        : now.getFullYear() + '-' + ('0' + (now.getMonth() + 1)).slice(-2) + '-' + ('0' + now.getDate()).slice(-2);
    var txnDate = document.getElementById('txn_date');
    if (txnDate) {
        if (window.ExpensePro && ExpensePro.utils && ExpensePro.utils.datePickers) {
            ExpensePro.utils.datePickers.sync(txnDate, today);
        } else {
            txnDate.value = today;
        }
    }

    
    var catSelect = document.getElementById('txn_category_id');
    if (catSelect) {
        catSelect.innerHTML = '<option value="">Select a category</option>';
        catSelect.value = '';
        catSelect.disabled = true;
    }

    var modal = document.getElementById('transaction-modal');
    if (modal) modal.classList.remove('hidden');
    refreshSubmitState();
}

function editTransaction(id) {
    transactionState.editingId = id;
    document.getElementById('modal-title').textContent = 'Edit Transaction';
    document.getElementById('form-error').classList.add('hidden');
    document.getElementById('submit-btn').textContent = 'Update Transaction';

    ajaxRequest('GET', BASE_URL + 'api/transactions.php?action=single&id=' + id)
        .then(function (response) {
            if (response.success && response.data) {
                var txn = response.data;

                var typeRadio = document.querySelector('input[name="txn_type"][value="' + txn.type + '"]');
                if (typeRadio) typeRadio.checked = true;

                loadCategoryOptions(txn.type, txn.category_id);

                document.getElementById('txn_amount').value = txn.amount;
                document.getElementById('txn_description').value = txn.description || '';
                var txnDateInput = document.getElementById('txn_date');
                if (txnDateInput) {
                    if (window.ExpensePro && ExpensePro.utils && ExpensePro.utils.datePickers) {
                        ExpensePro.utils.datePickers.sync(txnDateInput, txn.date);
                    } else {
                        txnDateInput.value = txn.date;
                    }
                }

                var modal = document.getElementById('transaction-modal');
                if (modal) modal.classList.remove('hidden');
                refreshSubmitState();
            } else {
                showToast('Failed to load transaction details.', 'error');
            }
        })
        .catch(function (error) {
            showToast(error.message || 'Failed to load transaction.', 'error');
        });
}

function loadCategoryOptions(type, selectedId) {
    var select = document.getElementById('txn_category_id');
    if (!select) return;

    select.innerHTML = '<option value="">Loading categories...</option>';
    select.disabled = true;

    ajaxRequest('GET', BASE_URL + 'api/categories.php?type=' + type)
        .then(function (response) {
            select.innerHTML = '<option value="">Select category</option>';

            if (response.success && response.data) {
                response.data.forEach(function (cat) {
                    var selected = (selectedId && cat.id == selectedId) ? ' selected' : '';
                    select.innerHTML += '<option value="' + cat.id + '"' + selected + '>' +
                        escapeHtml(cat.name) + '</option>';
                });
            }

            select.disabled = false;
            refreshSubmitState();
        })
        .catch(function () {
            select.innerHTML = '<option value="">Failed to load categories</option>';
            select.disabled = false;
            refreshSubmitState();
        });
}

function submitTransactionForm() {
    var form = document.getElementById('transaction-form');
    var errorDiv = document.getElementById('form-error');
    var submitBtn = document.getElementById('submit-btn');

    errorDiv.classList.add('hidden');

    var typeRadio = document.querySelector('input[name="txn_type"]:checked');
    var type = typeRadio ? typeRadio.value : '';
    var categoryId = document.getElementById('txn_category_id').value;
    var amount = document.getElementById('txn_amount').value;
    var description = document.getElementById('txn_description').value.trim();
    var date = document.getElementById('txn_date').value;

    var errors = [];
    if (window.ExpensePro && window.ExpensePro.validation) {
        var fieldErrors = window.ExpensePro.validation.validateTransaction({
            type: type,
            category_id: categoryId,
            amount: amount,
            date: date,
            description: description
        });
        Object.keys(fieldErrors).forEach(function (key) {
            errors.push(fieldErrors[key]);
        });
    } else {
        if (!type) errors.push('Please select income or expense.');
        if (!categoryId) errors.push('Please select a category.');
        if (!amount || isNaN(amount) || parseFloat(amount) <= 0) errors.push('Amount must be greater than ₹0.00.');
        if (!date) errors.push('Please select a date.');
    }

    if (errors.length > 0) {
        errorDiv.textContent = errors.join(' ');
        errorDiv.classList.remove('hidden');
        return;
    }

    var payload = {
        type: type,
        category_id: parseInt(categoryId),
        amount: parseFloat(amount),
        description: description,
        date: date
    };

    showButtonLoading('submit-btn');

    var method = transactionState.editingId ? 'PUT' : 'POST';
    if (transactionState.editingId) {
        payload.id = transactionState.editingId;
    }

    try {
        ajaxRequest(method, BASE_URL + 'api/transactions.php', payload)
        .then(function (response) {
            hideButtonLoading('submit-btn');
            showToast(response.message || 'Transaction saved!', 'success');
            haptic(15);
            closeTransactionModal();
            loadTransactions();
        })
        .catch(function (error) {
            hideButtonLoading('submit-btn');
            refreshSubmitState();
            var msgs = [];
            if (error.message) msgs.push(error.message);
            if (error.errors) {
                Object.values(error.errors).forEach(function (e) {
                    if (typeof e === 'string' && msgs.indexOf(e) === -1) msgs.push(e);
                });
            }
            if (msgs.length > 0) {
                errorDiv.innerHTML = msgs.map(function (m) { return '<p>' + escapeHtml(m) + '</p>'; }).join('');
                errorDiv.classList.remove('hidden');
            } else {
                showToast('Failed to save transaction.', 'error');
            }
        });
    } catch (err) {
        
        hideButtonLoading('submit-btn');
        refreshSubmitState();
        showToast('Failed to save transaction. Please try again.', 'error');
    }
}

function closeTransactionModal() {
    var modal = document.getElementById('transaction-modal');
    if (modal) modal.classList.add('hidden');
}

function initTransactionListEvents() {
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-txn-action], [data-txn-page]');
        if (!el) return;
        if (el.dataset.txnAction === 'edit') {
            editTransaction(parseInt(el.dataset.txnId, 10) || 0);
        } else if (el.dataset.txnAction === 'delete') {
            confirmDeleteTransaction(parseInt(el.dataset.txnId, 10) || 0);
        } else if (el.dataset.txnPage && !el.disabled) {
            loadTransactions(parseInt(el.dataset.txnPage, 10) || 1);
        }
    });
}

function confirmDeleteTransaction(id) {
    confirmDialog({
        title: 'Delete Transaction',
        message: 'Are you sure you want to delete this transaction? This action cannot be undone.',
        confirmLabel: 'Delete'
    }).then(function (confirmed) {
        if (!confirmed) return;
        ajaxRequest('DELETE', BASE_URL + 'api/transactions.php?id=' + id)
            .then(function (response) {
                showToast(response.message || 'Transaction deleted.', 'success');
                haptic([20, 40, 20]);
                loadTransactions();
            })
            .catch(function (error) {
                showToast(error.message || 'Failed to delete transaction.', 'error');
            });
    });
}

function initTransactionFilters() {
    var typeFilter = document.getElementById('filter-type');
    var searchInput = document.getElementById('filter-search');
    var dateFrom = document.getElementById('filter-date-from');
    var dateTo = document.getElementById('filter-date-to');
    var categoryFilter = document.getElementById('filter-category');
    var applyBtn = document.getElementById('filter-apply');
    var clearBtn = document.getElementById('filter-clear');

    if (typeFilter) {
        typeFilter.addEventListener('change', function () {
            transactionState.filters.type = this.value;
            updateFilterBadge();
            loadFilterCategories();
        });
    }

    if (applyBtn) {
        applyBtn.addEventListener('click', function () {
            readFilterInputs();
            transactionState.currentPage = 1;
            updateFilterBadge();
            closeFilterPanel();
            loadTransactions();
        });
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            transactionState.filters = { type: '', search: '', date_from: '', date_to: '', category_id: 0 };
            if (typeFilter) typeFilter.value = '';
            if (searchInput) searchInput.value = '';
            if (dateFrom) {
                dateFrom.value = '';
                if (window.ExpensePro && ExpensePro.utils && ExpensePro.utils.datePickers) ExpensePro.utils.datePickers.sync(dateFrom, '');
            }
            if (dateTo) {
                dateTo.value = '';
                if (window.ExpensePro && ExpensePro.utils && ExpensePro.utils.datePickers) ExpensePro.utils.datePickers.sync(dateTo, '');
            }
            if (categoryFilter) categoryFilter.value = '';
            transactionState.currentPage = 1;
            updateFilterBadge();
            loadFilterCategories();
            loadTransactions();
        });
    }

    if (searchInput) {
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && applyBtn) {
                applyBtn.click();
            }
        });

        var debouncedSearch = debounce(function () {
            readFilterInputs();
            transactionState.currentPage = 1;
            updateFilterBadge();
            loadTransactions();
        }, 400);
        searchInput.addEventListener('input', debouncedSearch);
    }
}

function readFilterInputs() {
    var searchInput = document.getElementById('filter-search');
    var dateFrom = document.getElementById('filter-date-from');
    var dateTo = document.getElementById('filter-date-to');
    var categoryFilter = document.getElementById('filter-category');
    transactionState.filters.search = searchInput ? searchInput.value.trim() : '';
    transactionState.filters.date_from = dateFrom ? dateFrom.value : '';
    transactionState.filters.date_to = dateTo ? dateTo.value : '';
    transactionState.filters.category_id = categoryFilter ? parseInt(categoryFilter.value) || 0 : 0;
}

function updateResultCount(pagination) {
    var el = document.getElementById('filter-result-count');
    if (!el) return;
    if (!pagination) {
        el.textContent = 'Could not load transactions.';
        return;
    }
    var total = pagination.total || 0;
    if (total === 0) {
        el.textContent = 'No results';
        return;
    }
    var start = (pagination.page - 1) * pagination.limit + 1;
    var end = Math.min(pagination.page * pagination.limit, total);
    el.innerHTML = '';
    var strong = document.createElement('strong');
    strong.textContent = start + '–' + end + ' of ' + total;
    el.appendChild(strong);
    el.appendChild(document.createTextNode(total === 1 ? ' transaction' : ' transactions'));
}

function updateFilterBadge() {
    var f = transactionState.filters;
    var count = 0;
    if (f.type) count++;
    if (f.search) count++;
    if (f.date_from || f.date_to) count++;
    if (f.category_id > 0) count++;

    var badge = document.getElementById('filter-count-badge');
    if (badge) {
        badge.textContent = count > 0 ? String(count) : '';
        badge.classList.toggle('hidden', count === 0);
        badge.classList.toggle('inline-flex', count > 0);
    }
    var triggerLabel = document.getElementById('filter-trigger-label');
    if (triggerLabel) {
        triggerLabel.textContent = count > 0 ? 'Filters (' + count + ')' : 'Filters';
    }
    var clearBtn = document.getElementById('filter-clear');
    if (clearBtn) {
        clearBtn.textContent = count > 0 ? 'Reset (' + count + ')' : 'Reset';
    }
    var activeEl = document.getElementById('filter-active-count');
    if (activeEl) {
        activeEl.textContent = count > 0
            ? (count + (count === 1 ? ' filter active' : ' filters active'))
            : 'No active filters';
    }
}

function openFilterPanel() {
    var panel = document.getElementById('filter-panel');
    if (!panel || !panel.classList.contains('hidden')) return;
    panel.classList.remove('hidden');
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-modal', 'true');
    lockScroll();
    haptic(10);
    var first = document.getElementById('filter-search');
    if (first) {
        try { first.focus({ preventScroll: true }); } catch (e) {}
    }
}

function closeFilterPanel() {
    var panel = document.getElementById('filter-panel');
    if (!panel || panel.classList.contains('hidden')) return;

    if (window.matchMedia && window.matchMedia('(min-width: 768px)').matches) return;
    panel.classList.add('hidden');
    panel.removeAttribute('role');
    panel.removeAttribute('aria-modal');
    unlockScroll();
}

function initFilterSheet() {
    var trigger = document.getElementById('filter-trigger');
    if (trigger) trigger.addEventListener('click', openFilterPanel);

    var closeBtn = document.getElementById('filter-close');
    if (closeBtn) closeBtn.addEventListener('click', closeFilterPanel);

    var panel = document.getElementById('filter-panel');
    if (panel) {
        var backdrop = panel.querySelector('[data-filter-close]');
        if (backdrop) backdrop.addEventListener('click', closeFilterPanel);
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeFilterPanel();
    });

    if (window.matchMedia) {
        var mq = window.matchMedia('(min-width: 768px)');
        var onChange = function (ev) {
            if (ev.matches) {
                var p = document.getElementById('filter-panel');
                if (p) {
                    p.classList.remove('hidden');
                    p.removeAttribute('role');
                    p.removeAttribute('aria-modal');
                }
                unlockScroll();
            }
        };
        if (typeof mq.addEventListener === 'function') mq.addEventListener('change', onChange);
    }
}

function loadFilterCategories() {
    var select = document.getElementById('filter-category');
    if (!select) return;

    var typeFilter = document.getElementById('filter-type');
    var type = typeFilter ? typeFilter.value : '';
    var currentVal = select.value;

    select.innerHTML = '<option value="">All Categories</option>';
    select.disabled = true;

    var url = BASE_URL + 'api/categories.php';
    if (type) url += '?type=' + type;

    ajaxRequest('GET', url)
        .then(function (response) {
            select.innerHTML = '<option value="">All Categories</option>';

            if (response.success && response.data) {
                response.data.forEach(function (cat) {
                    var opt = document.createElement('option');
                    opt.value = cat.id;
                    opt.textContent = cat.name;
                    select.appendChild(opt);
                });
            }

            if (currentVal) {
                select.value = currentVal;
            }
            select.disabled = false;
        })
        .catch(function () {
            select.innerHTML = '<option value="">All Categories</option>';
            select.disabled = false;
        });
}

function exportTransactions() {
    var params = new URLSearchParams();
    var f = transactionState.filters;
    if (f.type) params.set('type', f.type);
    if (f.search) params.set('search', f.search);
    if (f.date_from) params.set('date_from', f.date_from);
    if (f.date_to) params.set('date_to', f.date_to);
    if (f.category_id > 0) params.set('category_id', f.category_id);

    var url = BASE_URL + 'api/export.php?' + params.toString();
    var link = document.createElement('a');
    link.href = url;
    link.download = 'expensepro_transactions.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast('CSV downloaded!', 'success');
}

function exportTransactionsPdf() {
    if (typeof PDFEngine === 'undefined') {
        showToast('PDF engine is still loading. Please try again in a moment.', 'error');
        return;
    }
    showToast('Preparing PDF...', 'info', 2000);

    var params = new URLSearchParams();
    var f = transactionState.filters;
    if (f.type) params.set('type', f.type);
    if (f.search) params.set('search', f.search);
    if (f.date_from) params.set('date_from', f.date_from);
    if (f.date_to) params.set('date_to', f.date_to);
    if (f.category_id > 0) params.set('category_id', f.category_id);

    PDFEngine.fetchAllPages(BASE_URL + 'api/transactions.php?' + params.toString()).then(function (rows) {
        if (!rows || rows.length === 0) {
            showToast('No transactions match the current filters.', 'warning');
            return;
        }

        var incomePaise = 0;
        var expensePaise = 0;
        rows.forEach(function (t) {
            var paise = Math.round(parseFloat(t.amount) * 100) || 0;
            if (t.type === 'income') incomePaise += paise;
            else expensePaise += paise;
        });
        var summary = {
            total_income: incomePaise / 100,
            total_expense: expensePaise / 100,
            balance: (incomePaise - expensePaise) / 100
        };
        var subtitle = 'Filtered transactions' +
            (f.date_from || f.date_to ? ' · ' + (f.date_from || '…') + ' — ' + (f.date_to || '…') : '');
        PDFEngine.renderReport(
            PDFEngine.wrap(
                PDFEngine.docHeader('Transactions', subtitle) +
                PDFEngine.summaryGrid(summary)
            ),
            rows,
            PDFEngine.filename('Transactions', f.date_from || 'all', f.date_to || 'all'),
            rows.length + ' transaction(s) shown'
        );
    }).catch(function (error) {

        showToast((error && error.message) || 'Could not load transactions. PDF export aborted.', 'error');
    });
}

function initExportButton() {
    var exportBtn = document.getElementById('export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            exportTransactions();
        });
    }

    var exportPdfBtn = document.getElementById('export-pdf-btn');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', function () {
            exportTransactionsPdf();
        });
    }

    var exportCsvBtn = document.getElementById('export-csv-btn');
    if (exportCsvBtn) {
        exportCsvBtn.addEventListener('click', function () {
            exportTransactions();
        });
    }
    var exportPdfBtnMobile = document.getElementById('export-pdf-btn-mobile');
    if (exportPdfBtnMobile) {
        exportPdfBtnMobile.addEventListener('click', function () {
            exportTransactionsPdf();
        });
    }
}
