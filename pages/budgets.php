<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Budgets';
$pageScripts[] = 'pages/budgets.js';
$budgetsData = [];
$error = '';
$success = '';
?>

<main class="flex-1 p-4 md:p-6 lg:p-8 max-w-7xl mx-auto w-full">

    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Budgets</h1>
            <p class="text-sm text-slate-500 mt-1">Set and track your spending limits</p>
        </div>
        <button type="button" data-ep-action="openCreateBudgetModal"
                class="hidden md:inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all shadow-md shadow-indigo-600/20 hover:shadow-lg active:scale-95 sm:hover:scale-100">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Budget
        </button>
    </div>

    
    <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 mb-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h2 class="text-sm font-semibold text-slate-900">Active budget summary</h2>
            <p class="text-xs text-slate-400" id="summary-scope">No budgets yet</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Budgeted</p>
                <p class="text-2xl font-bold text-slate-900 mt-1" id="summary-budgeted">₹0</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Spent</p>
                <p class="text-2xl font-bold text-rose-600 mt-1" id="summary-spent">₹0</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Remaining</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1" id="summary-remaining">₹0</p>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Status</p>
                <p class="text-2xl font-bold text-indigo-600 mt-1" id="summary-status">On Track</p>
            </div>
        </div>
    </div>

    
    <div class="md:hidden mb-4">
        <button type="button" data-ep-action="openCreateBudgetModal"
                class="w-full inline-flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all shadow-md shadow-indigo-600/20 active:scale-95">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Budget
        </button>
    </div>

    
    <div id="budgets-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        
<div class="col-span-full text-center py-12 text-slate-400 text-sm"><img src="<?= BASE_URL ?>assets/icons/SVG/3-dots-fade.svg" alt="Loading" width="36" height="36" class="mx-auto mb-2">Loading budgets...</div>
    </div>

    
    <div id="budget-modal" class="hidden fixed inset-0 ep-layer-modal">
        <div class="absolute inset-0 bg-white/80 backdrop-blur-md modal-backdrop" data-ep-action="closeBudgetModal"></div>
        <div class="modal-shell">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto modal-content modal-panel">
                <div class="p-5 md:p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 id="budget-modal-title" class="text-lg font-bold text-slate-900">Add Budget</h2>
                        <button type="button" data-ep-action="closeBudgetModal" aria-label="Close dialog" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div id="budget-form-error" class="hidden bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm mb-4"></div>

                    <form id="budget-form" class="space-y-4" autocomplete="off">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" id="budget-id" name="id" value="">

                        <div>
                            <label for="budget-category" class="block text-sm font-medium text-slate-700 mb-1.5">Category (optional)</label>
                            <select id="budget-category" name="category_id"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                                <option value="">All Expenses</option>
                            </select>
                            <p class="text-xs text-slate-400 mt-1">Leave empty to set a budget for all expenses combined</p>
                        </div>

                        <div>
                            <label for="budget-amount" class="block text-sm font-medium text-slate-700 mb-1.5">Budget Amount (₹)</label>
                            <input type="number" id="budget-amount" name="amount" step="0.01" min="0.01" required
                                   placeholder="0.00"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>

                        <div>
                            <label for="budget-period" class="block text-sm font-medium text-slate-700 mb-1.5">Period</label>
                            <select id="budget-period" name="period" required
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                                <option value="monthly">Monthly</option>
                                <option value="weekly">Weekly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>

                        <button type="submit" id="budget-submit-btn"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-xl transition-all duration-200 shadow-md shadow-indigo-600/20 hover:shadow-lg active:scale-95 sm:hover:scale-100">
                            Add Budget
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</main>
