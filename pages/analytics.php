<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Analytics';
$pageScripts = ['charts.js'];
$pageScripts[] = 'pages/analytics.js';
$summary = null;
$categoryBreakdown = [];
$balanceTrend = null;
$error = '';
$success = '';
?>

<main class="flex-1 p-4 md:p-6 lg:p-8 max-w-7xl mx-auto w-full">

    
    <div class="flex items-center justify-between mb-6" >
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Analytics</h1>
            <p class="text-sm text-slate-500 mt-1">In-depth financial insights and patterns</p>
        </div>
        <button type="button" data-ep-action="refreshAnalytics"
                id="analytics-refresh-btn"
                class="hidden md:inline-flex items-center gap-2 bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 text-sm font-medium px-3 py-2.5 rounded-xl transition-all">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182"/></svg>
            Refresh
        </button>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

        
        <div class="bg-white rounded-xl border border-slate-100 p-5 card-hover" >
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75"/></svg>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Total Income</p>
                    <p class="text-2xl font-bold text-emerald-600" id="stat-income">&#8377;0</p>
                </div>
            </div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" id="income-bar" style="width: 0%"></div>
            </div>
        </div>

        
        <div class="bg-white rounded-xl border border-slate-100 p-5 card-hover" >
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 rounded-xl bg-rose-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-rose-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75"/></svg>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Total Expense</p>
                    <p class="text-2xl font-bold text-rose-600" id="stat-expense">&#8377;0</p>
                </div>
            </div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-rose-500 rounded-full" id="expense-bar" style="width: 0%"></div>
            </div>
        </div>

        
        <div class="bg-white rounded-xl border border-slate-100 p-5 card-hover" >
            <div class="flex items-center gap-3 mb-3">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15"/></svg>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Net Balance</p>
                    <p class="text-2xl font-bold text-slate-900" id="stat-balance">&#8377;0</p>
                </div>
            </div>
            <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-500 rounded-full" id="balance-bar" style="width: 0%"></div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        
        <div class="bg-white rounded-xl border border-slate-100 p-5" >
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-900">Net Balance Trend</h2>
                <span class="text-xs text-slate-400">Last 12 months</span>
            </div>
            <div id="line-chart"></div>
        </div>

        
        <div class="bg-white rounded-xl border border-slate-100 p-5" >
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-900">Income vs Expense</h2>
                <span class="text-xs text-slate-400">Last 12 months</span>
            </div>
            <div id="multi-line-chart"></div>
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-slate-100 p-5" >
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-slate-900">Category Breakdown</h2>
            <span class="text-xs text-slate-400">Expense categories</span>
        </div>
        <div id="category-breakdown-list" class="space-y-3">
            
            <div class="text-center py-8 text-slate-400 text-sm"><img src="<?= BASE_URL ?>assets/icons/SVG/3-dots-fade.svg" alt="Loading" width="36" height="36" class="mx-auto mb-2">Loading breakdown...</div>
        </div>
    </div>

</main>
