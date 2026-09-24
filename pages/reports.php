<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Reports';
$pageScripts = ['charts.js', 'pdf.js', 'pages/reports.js'];
$reportData = null;
$error = '';
$success = '';
?>

<main class="flex-1 p-4 md:p-6 lg:p-8 max-w-7xl mx-auto w-full">

    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Reports</h1>
            <p class="text-sm text-slate-500 mt-1">Generate and download financial reports</p>
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-slate-100 p-5 mb-6">
        <div class="flex flex-col sm:flex-row items-end gap-4">
            <div class="flex-1 w-full sm:w-auto">
                <label class="block text-sm font-medium text-slate-700 mb-1" for="report-date-from">From</label>
                <input type="date" id="report-date-from" value="<?= htmlspecialchars(date('Y-m-d', strtotime('-30 days')), ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
            </div>
            <div class="flex-1 w-full sm:w-auto">
                <label class="block text-sm font-medium text-slate-700 mb-1" for="report-date-to">To</label>
                <input type="date" id="report-date-to" value="<?= htmlspecialchars(date('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition-all">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="button" data-ep-action="loadReport" class="flex-1 sm:flex-none px-5 py-2.5 bg-indigo-600 text-white text-sm font-medium rounded-xl hover:bg-indigo-700 transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    Generate
                </button>
                <button type="button" data-ep-action="setQuickRange" data-ep-arg="week" class="px-3 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition-all">7D</button>
                <button type="button" data-ep-action="setQuickRange" data-ep-arg="month" class="px-3 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition-all">30D</button>
                <button type="button" data-ep-action="setQuickRange" data-ep-arg="quarter" class="px-3 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition-all">90D</button>
                <button type="button" data-ep-action="setQuickRange" data-ep-arg="year" class="px-3 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-xl hover:bg-slate-50 transition-all">1Y</button>
            </div>
        </div>
    </div>

    
    <div id="report-summary-cards" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 hidden">
        <div class="bg-white rounded-xl border border-slate-100 p-5 card-hover">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75"/></svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Income</p>
                    <p class="text-lg font-bold text-emerald-600" id="rpt-income">-</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-5 card-hover">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-rose-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75"/></svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Expenses</p>
                    <p class="text-lg font-bold text-rose-600" id="rpt-expense">-</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-5 card-hover">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15"/></svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Savings</p>
                    <p class="text-lg font-bold text-slate-900" id="rpt-balance">-</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-5 card-hover">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008V18zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zM8.25 6h7.5v2.25h-7.5V6zM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 002.25 2.25h10.5a2.25 2.25 0 002.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0012 2.25z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Transactions</p>
                    <p class="text-lg font-bold text-slate-900" id="rpt-count">-</p>
                </div>
            </div>
        </div>
    </div>

    
    <div id="report-charts-section" class="hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl border border-slate-100 p-5">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Expense by Category</h2>
                <div id="rpt-category-chart"></div>
            </div>
            <div class="bg-white rounded-xl border border-slate-100 p-5">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Monthly Income vs Expense</h2>
                <div id="rpt-trend-chart"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-100 p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-slate-900">Category-Wise Monthly Comparison</h2>
                <span class="text-xs text-slate-400">Monthly expense breakdown by category</span>
            </div>
            <div id="rpt-category-comparison-chart"></div>
        </div>
    </div>

    
    <div id="report-export-section" class="hidden">
        <div class="flex items-center gap-3 mb-4">
            <h2 class="text-sm font-semibold text-slate-900">Export Report</h2>
            <button type="button" data-ep-action="exportCSV" class="flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition-all">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                CSV
            </button>
            <button type="button" data-ep-action="exportPDF" class="flex items-center gap-2 px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-xl hover:bg-rose-700 transition-all">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                PDF
            </button>
        </div>

    </div>

    
    <div id="report-empty" class="text-center py-16">
        <svg class="w-16 h-16 mx-auto mb-4 text-slate-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.8"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
        <h2 class="text-lg font-semibold text-slate-700 mb-1">No report generated yet</h2>
        <p class="text-sm text-slate-500">Select a date range above and click <strong>Generate</strong> to create your report.</p>
    </div>

</main>
