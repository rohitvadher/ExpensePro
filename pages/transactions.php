<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Transactions';
$pageScripts = ['pdf.js', 'transactions.js'];
$urlType = isset($_GET['type']) ? htmlspecialchars($_GET['type'], ENT_QUOTES, 'UTF-8') : '';
$transactions = [];
$error = '';
$success = '';
?>


<div id="txn-url-type-data" data-url-type="<?= htmlspecialchars($urlType, ENT_QUOTES, 'UTF-8') ?>"></div>

<main class="flex-1 p-4 md:p-6 lg:p-8 max-w-7xl mx-auto w-full">

    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Transactions</h1>
            <p class="text-sm text-slate-500 mt-1">Manage your income and expense entries</p>
        </div>
        <div class="flex items-center gap-2">
            
            <button type="button" id="export-btn"
                    class="hidden md:inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-medium px-3 py-2.5 rounded-xl transition-all"
                    title="Export as CSV">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                CSV
            </button>
            
            <button type="button" id="export-pdf-btn"
                    class="hidden md:inline-flex items-center gap-2 bg-rose-50 border border-rose-200 text-rose-700 hover:bg-rose-100 text-sm font-medium px-3 py-2.5 rounded-xl transition-all"
                    title="Export as PDF">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                PDF
            </button>
            
            <button type="button" data-ep-action="openCreateModal"
                    class="hidden md:inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all shadow-md shadow-indigo-600/20 hover:shadow-lg active:scale-95 sm:hover:scale-100">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add Transaction
            </button>
        </div>
    </div>

    
    <div class="md:hidden mb-4">
        <button id="filter-trigger" type="button"
                class="w-full inline-flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium px-4 py-3 rounded-xl transition-all shadow-sm active:scale-95"
                aria-haspopup="dialog" aria-controls="filter-panel">
            <svg class="w-4 h-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0l2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z"/></svg>
            <span id="filter-trigger-label">Filters</span>
            <span id="filter-count-badge" class="hidden min-w-[20px] h-5 px-1.5 rounded-full bg-indigo-600 text-white text-[11px] font-bold items-center justify-center"></span>
        </button>
    </div>

    <div id="filter-panel" class="ep-filter-panel hidden md:block">
        <div class="ep-filter-backdrop md:hidden" data-filter-close></div>
        <div class="ep-filter-card">
            <div class="flex items-center justify-between mb-4 md:hidden">
                <h2 id="filter-panel-title" class="text-base font-bold text-slate-900">Filters</h2>
                <button id="filter-close" type="button" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Close filters">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="grid grid-cols-1 min-[420px]:grid-cols-2 md:grid-cols-6 gap-3">

            
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1" for="filter-type">Type</label>
                <select id="filter-type"
                        class="w-full px-3 py-2.5 rounded-lg border border-slate-300 bg-slate-50 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                    <option value="">All Types</option>
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
            </div>

            
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1" for="filter-search">Search</label>
                <input type="text" id="filter-search"
                       placeholder="Search description..."
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
            </div>

            
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1" for="filter-category">Category</label>
                <select id="filter-category" class="w-full px-3 py-2.5 rounded-lg border border-slate-300 bg-slate-50 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                    <option value="">All Categories</option>
                </select>
            </div>

            
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1" for="filter-date-from">From</label>
                <input type="date" id="filter-date-from"
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 bg-slate-50 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
            </div>

            
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1" for="filter-date-to">To</label>
                <input type="date" id="filter-date-to"
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 bg-slate-50 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
            </div>

            
            <div class="flex items-end gap-2">
                <button id="filter-apply" type="button"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-3 py-2.5 rounded-lg transition-all min-h-[44px]">
                    Apply
                </button>
                <button id="filter-clear" type="button"
                        class="flex-1 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium px-3 py-2.5 rounded-lg transition-all min-h-[44px]">
                    Reset
                </button>
            </div>
            </div>
            <div class="ep-filter-meta hidden md:flex">
                <p class="ep-filter-count" id="filter-result-count" role="status">Loading transactions…</p>
                <p class="ep-filter-count" id="filter-active-count"></p>
            </div>
            <div class="flex items-center gap-2 mt-3 md:hidden">
                <button id="export-csv-btn" type="button"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-white border border-slate-200 text-slate-600 text-sm font-medium px-3 py-2.5 rounded-lg transition-all min-h-[44px]">
                    Export CSV
                </button>
                <button id="export-pdf-btn-mobile" type="button"
                        class="flex-1 inline-flex items-center justify-center gap-2 bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium px-3 py-2.5 rounded-lg transition-all min-h-[44px]">
                    Export PDF
                </button>
            </div>
        </div>
    </div>

    
    <div id="transactions-list" class="grid grid-cols-1 gap-3">
        
        <div class="text-center py-12 text-slate-400 text-sm"><img src="<?= BASE_URL ?>assets/icons/SVG/3-dots-fade.svg" alt="Loading" width="36" height="36" class="mx-auto mb-2">Loading transactions...</div>
    </div>

    
    <div id="transactions-pagination" class="mt-6"></div>

    
    <div id="transaction-modal" class="hidden fixed inset-0 ep-layer-modal">
        <div class="absolute inset-0 bg-white/80 backdrop-blur-md modal-backdrop" data-ep-action="closeTransactionModal"></div>
        <div class="modal-shell">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto modal-content modal-panel">
                <div class="p-5 md:p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 id="modal-title" class="text-lg font-bold text-slate-900">Add Transaction</h2>
                        <button type="button" data-ep-action="closeTransactionModal" aria-label="Close dialog" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div id="form-error" class="hidden bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm mb-4"></div>

                    <form id="transaction-form" class="space-y-4">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Transaction Type</label>
                            <div class="flex gap-2">
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="txn_type" value="income" class="hidden peer" aria-label="Income">
                                    <div class="text-center px-4 py-2.5 rounded-xl border-2 border-slate-200 text-sm font-medium text-slate-600 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 transition-all">
                                        Income
                                    </div>
                                </label>
                                <label class="flex-1 cursor-pointer">
                                    <input type="radio" name="txn_type" value="expense" class="hidden peer" aria-label="Expense">
                                    <div class="text-center px-4 py-2.5 rounded-xl border-2 border-slate-200 text-sm font-medium text-slate-600 peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 transition-all">
                                        Expense
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="txn_category_id" class="block text-sm font-medium text-slate-700 mb-1.5">Category</label>
                            <select id="txn_category_id" name="category_id" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                                <option value="">Select a category</option>
                            </select>
                        </div>

                        <div>
                            <label for="txn_amount" class="block text-sm font-medium text-slate-700 mb-1.5">Amount (&#8377;)</label>
                            <input type="number" id="txn_amount" name="amount" step="0.01" min="0.01" required
                                   placeholder="0.00"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>

                        <div>
                            <label for="txn_description" class="block text-sm font-medium text-slate-700 mb-1.5">Description (optional)</label>
                            <input type="text" id="txn_description" name="description"
                                   placeholder="What was this for?"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>

                        <div>
                            <label for="txn_date" class="block text-sm font-medium text-slate-700 mb-1.5">Date</label>
                            <input type="date" id="txn_date" name="date" required
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>

                        <button type="submit" id="submit-btn"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-xl transition-all duration-200 shadow-md shadow-indigo-600/20 hover:shadow-lg active:scale-95 sm:hover:scale-100">
                            Add Transaction
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</main>
