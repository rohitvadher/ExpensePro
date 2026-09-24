<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Import Transactions';
$pageScripts[] = 'pages/import.js';
$parsedCSV = [];
$selectedFile = null;
$error = '';
$success = '';
?>

<main class="flex-1 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto w-full">

    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Import Transactions</h1>
            <p class="text-sm text-slate-500 mt-1">Bulk import your transactions from a CSV file</p>
        </div>
        <a href="<?= BASE_URL ?>assets/samples/expensepro-sample.csv" download="expensepro-sample.csv"
                class="inline-flex items-center gap-2 text-sm font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-4 py-2.5 rounded-xl transition-all shadow-sm">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Download Sample CSV
        </a>
    </div>

    
    <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 mb-6">
        <h2 class="text-sm font-semibold text-slate-900 mb-2">CSV Format Instructions</h2>
        <p class="text-xs text-slate-500 mb-3">Your CSV file should have the following columns in this order:</p>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Column</th>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Name</th>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Description</th>
                        <th class="text-left px-3 py-2 font-medium text-slate-600">Example</th>
                    </tr>
                </thead>
                <tbody class="text-slate-500">
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 font-mono text-indigo-600">A</td>
                        <td class="px-3 py-2 font-medium text-slate-700">Date</td>
                        <td class="px-3 py-2">Transaction date (DD/MM/YYYY or YYYY-MM-DD)</td>
                        <td class="px-3 py-2 font-mono">01/04/2026</td>
                    </tr>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 font-mono text-indigo-600">B</td>
                        <td class="px-3 py-2 font-medium text-slate-700">Type</td>
                        <td class="px-3 py-2">"income" or "expense"</td>
                        <td class="px-3 py-2 font-mono">expense</td>
                    </tr>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 font-mono text-indigo-600">C</td>
                        <td class="px-3 py-2 font-medium text-slate-700">Category</td>
                        <td class="px-3 py-2">Must match an existing category name</td>
                        <td class="px-3 py-2 font-mono">Groceries</td>
                    </tr>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 font-mono text-indigo-600">D</td>
                        <td class="px-3 py-2 font-medium text-slate-700">Amount</td>
                        <td class="px-3 py-2">Positive number (no ₹ symbol)</td>
                        <td class="px-3 py-2 font-mono">1500.00</td>
                    </tr>
                    <tr class="border-t border-slate-100">
                        <td class="px-3 py-2 font-mono text-indigo-600">E</td>
                        <td class="px-3 py-2 font-medium text-slate-700">Description</td>
                        <td class="px-3 py-2">Optional description text</td>
                        <td class="px-3 py-2 font-mono">Weekly groceries</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    
    <div id="upload-area"
         class="bg-white rounded-xl border-2 border-dashed border-slate-200 p-8 md:p-12 text-center mb-6 transition-all cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30"
        >
        <div class="max-w-sm mx-auto">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
            <p class="text-sm font-medium text-slate-700 mb-1">Drop your CSV file here or click to browse</p>
            <p class="text-xs text-slate-400 mb-4">Supports .csv files up to 5 MB</p>
            <input type="file" id="csv-file-input" accept=".csv" class="hidden">
            <button type="button" id="choose-file-btn"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-all shadow-md shadow-indigo-600/20">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                Choose File
            </button>
            <p id="file-name-display" class="text-xs text-slate-500 mt-3 hidden"></p>
        </div>
    </div>

    
    <div id="preview-section" class="hidden mb-6">
        <div class="bg-white rounded-xl border border-slate-100 overflow-hidden">
            <div class="p-4 md:p-5 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-900">Preview</h2>
                    <div class="flex items-center gap-2">
                        <span id="preview-count" class="text-xs text-slate-400">0 rows</span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto max-h-64 overflow-y-auto">
                <table class="w-full text-xs">
                    <thead class="bg-slate-50 sticky top-0">
                        <tr>
                            <th class="text-left px-4 py-2 font-medium text-slate-600">#</th>
                            <th class="text-left px-4 py-2 font-medium text-slate-600">Date</th>
                            <th class="text-left px-4 py-2 font-medium text-slate-600">Type</th>
                            <th class="text-left px-4 py-2 font-medium text-slate-600">Category</th>
                            <th class="text-left px-4 py-2 font-medium text-slate-600">Amount</th>
                            <th class="text-left px-4 py-2 font-medium text-slate-600">Description</th>
                            <th class="text-left px-4 py-2 font-medium text-slate-600">Status</th>
                        </tr>
                    </thead>
                    <tbody id="preview-table-body"></tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button type="button" id="cancel-import-btn"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                    Cancel
                </button>
                <button type="button" id="confirm-import-btn"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-xl transition-all shadow-md shadow-indigo-600/20">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    Import All
                </button>
            </div>
        </div>
    </div>

    
    <div id="import-result" class="hidden mb-6">
        <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5">
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Import Results</h2>
            <div class="grid grid-cols-4 gap-3 mb-4">
                <div class="bg-slate-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-slate-400">Total</p>
                    <p class="text-xl font-bold text-slate-900" id="result-total">0</p>
                </div>
                <div class="bg-emerald-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-emerald-400">Imported</p>
                    <p class="text-xl font-bold text-emerald-600" id="result-imported">0</p>
                </div>
                <div class="bg-amber-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-amber-400">Skipped</p>
                    <p class="text-xl font-bold text-amber-600" id="result-skipped">0</p>
                </div>
                <div class="bg-rose-50 rounded-xl p-3 text-center">
                    <p class="text-xs text-rose-400">Errors</p>
                    <p class="text-xl font-bold text-rose-600" id="result-errors">0</p>
                </div>
            </div>
            <div id="result-errors-list" class="space-y-1"></div>
            <button type="button" id="result-dismiss-btn"
                    class="mt-3 px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                Dismiss
            </button>
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-slate-900">Import History</h2>
            <span class="text-xs text-slate-400">Recent imports</span>
        </div>
        <div id="import-history-list" class="space-y-2">
            <div class="text-center py-6 text-slate-400 text-sm">No imports yet.</div>
        </div>
    </div>

</main>
