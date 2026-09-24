<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Dashboard';
$pageScripts = ['dashboard.js'];
$stats = null;
$recentTransactions = [];
$error = '';
$success = '';
?>


<main class="flex-1 p-4 md:p-6 lg:p-8 max-w-7xl mx-auto w-full">

    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">Your financial overview at a glance</p>
        </div>
        <a href="<?= htmlspecialchars(BASE_URL . '?page=transactions', ENT_QUOTES, 'UTF-8') ?>"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all shadow-md shadow-indigo-600/20 hover:shadow-lg active:scale-95 sm:hover:scale-100">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            <span class="hidden sm:inline">Add Transaction</span>
            <span class="sm:hidden">Add</span>
        </a>
    </div>

    
    <div class="flex items-center gap-3 mb-6 overflow-x-auto pb-1">
        
        <a href="<?= htmlspecialchars(BASE_URL . '?page=transactions', ENT_QUOTES, 'UTF-8') ?>" class="flex-shrink-0 inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 hover:border-indigo-300 hover:text-indigo-600 text-sm font-medium px-4 py-2.5 rounded-xl transition-all">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Quick Add
        </a>
        
        <a href="<?= htmlspecialchars(BASE_URL . '?page=reports', ENT_QUOTES, 'UTF-8') ?>" class="flex-shrink-0 inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-700 hover:border-indigo-300 hover:text-indigo-600 text-sm font-medium px-4 py-2.5 rounded-xl transition-all">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            Reports
        </a>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        
        <div class="stat-card bg-white rounded-xl border border-slate-100 p-4 md:p-5 card-hover">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Income</span>
                <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75"/></svg>
                </div>
            </div>
            <p class="counter text-2xl font-bold text-emerald-600" id="stat-income" data-target="0">&#8377;0</p>
            <p class="text-xs text-slate-400 mt-1">Total income</p>
        </div>

        
        <div class="stat-card bg-white rounded-xl border border-slate-100 p-4 md:p-5 card-hover">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Expense</span>
                <div class="w-9 h-9 rounded-lg bg-rose-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-rose-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75"/></svg>
                </div>
            </div>
            <p class="counter text-2xl font-bold text-rose-600" id="stat-expense" data-target="0">&#8377;0</p>
            <p class="text-xs text-slate-400 mt-1">Total expense</p>
        </div>

        
        <div class="stat-card ep-stat-balance bg-white rounded-xl border border-slate-100 p-4 md:p-5 card-hover">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Balance</span>
                <div class="w-9 h-9 rounded-lg bg-indigo-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15"/></svg>
                </div>
            </div>
            <p class="counter text-2xl font-bold text-slate-900" id="stat-balance">&#8377;0</p>
            <p class="text-xs text-slate-400 mt-1">Income − Expense</p>
        </div>

        
        <div class="stat-card bg-white rounded-xl border border-slate-100 p-4 md:p-5 card-hover">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Transactions</span>
                <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                </div>
            </div>
            <p class="counter text-2xl font-bold text-slate-900" id="stat-count" data-target="0">0</p>
            <p class="text-xs text-slate-400 mt-1">Total entries</p>
        </div>
    </div>

    
    <div id="budget-progress-widget" class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 mb-6 hidden">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-slate-900">Monthly Budget</h2>
            <a href="<?= htmlspecialchars(BASE_URL . '?page=profile', ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                Manage &rarr;
            </a>
        </div>
        <div id="budget-progress-content" class="space-y-4">
            
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-slate-900">Recent Transactions</h2>
            <a href="<?= htmlspecialchars(BASE_URL . '?page=transactions', ENT_QUOTES, 'UTF-8') ?>" class="text-xs text-indigo-600 hover:text-indigo-700 font-medium">
                View All &rarr;
            </a>
        </div>
        <div id="recent-transactions" class="space-y-3">
            
            <div class="text-center py-8 text-slate-400 text-sm"><img src="<?= BASE_URL ?>assets/icons/SVG/3-dots-fade.svg" alt="Loading" width="36" height="36" class="mx-auto mb-2">Loading transactions...</div>
        </div>
    </div>

</main>
