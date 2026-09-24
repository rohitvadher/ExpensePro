<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Help & Guide';
$pageScripts = [];

$helpIconStyles = [
    'indigo'  => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-500'],
    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-500'],
    'amber'   => ['bg' => 'bg-amber-50',   'text' => 'text-amber-500'],
    'violet'  => ['bg' => 'bg-violet-50',  'text' => 'text-violet-500'],
    'teal'    => ['bg' => 'bg-teal-50',    'text' => 'text-teal-500'],
    'sky'     => ['bg' => 'bg-sky-50',     'text' => 'text-sky-500'],
    'rose'    => ['bg' => 'bg-rose-50',    'text' => 'text-rose-500'],
    'slate'   => ['bg' => 'bg-slate-50',   'text' => 'text-slate-500'],
];

$helpIcon = function (string $path, string $color) use ($helpIconStyles): string {
    $style = $helpIconStyles[$color] ?? $helpIconStyles['slate'];
    return '<div class="w-9 h-9 rounded-xl ' . $style['bg'] . ' flex items-center justify-center flex-shrink-0">' .
        '<svg class="w-5 h-5 ' . $style['text'] . '" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">' .
        '<path stroke-linecap="round" stroke-linejoin="round" d="' . $path . '"/></svg></div>';
};

$sections = [
    'getting-started' => 'Getting Started',
    'transactions'    => 'Transactions',
    'categories'      => 'Categories',
    'budgets'         => 'Budgets',
    'reports'         => 'Reports',
    'import'          => 'CSV Import',
    'notifications'   => 'Notifications',
    'pwa'             => 'PWA / Install',
    'account'         => 'Account & Security',
    'faq'             => 'FAQ',
];
?>

<main class="flex-1 p-4 md:p-6 lg:p-8 max-w-4xl mx-auto w-full">

    
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900">Help &amp; User Guide</h1>
        <p class="text-sm text-slate-500 mt-1">Everything you need to know to get the most out of ExpensePro</p>
    </div>

    
    <div class="flex flex-wrap gap-2 mb-8">
        <?php foreach ($sections as $id => $label): ?>
        <a href="#<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" class="text-xs font-medium px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:border-indigo-300 hover:text-indigo-600 transition-all"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>
    </div>

    
    <section id="getting-started" class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <?= $helpIcon('M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25', 'indigo') ?>
            <h2 class="text-lg font-bold text-slate-900">Getting Started</h2>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-5">
            <p class="text-sm text-slate-600 mb-4">ExpensePro is a personal finance tracker for daily income and expenses. Follow these five steps:</p>
            <ol class="space-y-3 text-sm text-slate-600">
                <li class="flex gap-3"><span class="ep-step-num">1</span><span><strong>Create your account</strong> — register once; 16 default categories are added automatically and your data stays private to your account.</span></li>
                <li class="flex gap-3"><span class="ep-step-num">2</span><span><strong>Review categories</strong> — rename, recolor, or add income/expense categories. Names are unique within each type.</span></li>
                <li class="flex gap-3"><span class="ep-step-num">3</span><span><strong>Add your first transaction</strong> — use the + button (mobile) or Add Transaction (desktop). Save frequent entries as quick templates.</span></li>
                <li class="flex gap-3"><span class="ep-step-num">4</span><span><strong>Create a budget</strong> — weekly, monthly, or yearly, per category or overall. You will be alerted when spending exceeds it.</span></li>
                <li class="flex gap-3"><span class="ep-step-num">5</span><span><strong>Review analytics</strong> — check trends and breakdowns, then export a CSV or PDF report whenever you need one.</span></li>
            </ol>
        </div>
    </section>

    
    <section id="transactions" class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <?= $helpIcon('M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5', 'emerald') ?>
            <h2 class="text-lg font-bold text-slate-900">Transactions</h2>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 space-y-3">
            <p class="text-sm text-slate-600">Each transaction has a type (income or expense), an amount, a date, and a category. Amounts must be positive with at most two decimals.</p>
            <ul class="space-y-2 text-sm text-slate-600">
                <li><strong>Add</strong> — open the Add Transaction form, pick a type first so the category list matches, then save.</li>
                <li><strong>Edit / Delete</strong> — use the row actions. Deleting asks for confirmation and cannot be undone.</li>
                <li><strong>Filters</strong> — on desktop the filter card stays inline; on mobile tap <strong>Filters</strong> to open the filter sheet. The badge shows how many filters are active. Search is debounced as you type.</li>
                <li><strong>Export</strong> — download the filtered list as CSV, or generate a formatted PDF built from the same rows.</li>
            </ul>
            <div class="bg-indigo-50 rounded-xl p-3 text-xs text-indigo-700">
                Tip: press <kbd class="bg-white border border-indigo-200 px-1.5 py-0.5 rounded font-mono">Ctrl+K</kbd> (or <kbd class="bg-white border border-indigo-200 px-1.5 py-0.5 rounded font-mono">Cmd+K</kbd> on Mac) for quick navigation anywhere in the app.
            </div>
        </div>
    </section>

    
    <section id="categories" class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <?= $helpIcon('M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z', 'amber') ?>
            <h2 class="text-lg font-bold text-slate-900">Categories</h2>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 space-y-3">
            <p class="text-sm text-slate-600">Categories organize transactions and drive chart colors. Switch between the Income and Expense tabs to manage each type.</p>
            <ul class="space-y-2 text-sm text-slate-600">
                <li><strong>Colors and icons</strong> — pick any hex color and one of the built-in icons; colors are validated before display.</li>
                <li><strong>Safe delete</strong> — a category with transactions cannot be deleted until you move (reassign) those transactions to another category of the same type.</li>
                <li><strong>Usage counts</strong> — each card shows how many transactions used it this month.</li>
            </ul>
        </div>
    </section>

    
    <section id="budgets" class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <?= $helpIcon('M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6zM13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z', 'violet') ?>
            <h2 class="text-lg font-bold text-slate-900">Budgets</h2>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 space-y-3">
            <p class="text-sm text-slate-600">Budgets set spending limits for weekly, monthly, or yearly periods — per expense category, or overall when no category is chosen.</p>
            <ul class="space-y-2 text-sm text-slate-600">
                <li><strong>Progress</strong> — green means on track, amber means nearly full, red means over budget. Only expenses in the budget's period count.</li>
                <li><strong>Summary</strong> — the top card combines your active budgets and names the periods it covers, so mixed weekly/monthly budgets are never mislabeled.</li>
                <li><strong>Alerts</strong> — exceeding a budget creates an over-budget notification (at most once per budget per day).</li>
                <li><strong>Duplicates</strong> — one budget per category and period; editing an existing budget replaces it.</li>
            </ul>
        </div>
    </section>

    
    <section id="reports" class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <?= $helpIcon('M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z', 'teal') ?>
            <h2 class="text-lg font-bold text-slate-900">Reports</h2>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 space-y-3">
            <p class="text-sm text-slate-600">Pick any date range — or a 7/30/90/365-day shortcut — and press Generate. Every number comes from the same calculation rules as the dashboard.</p>
            <ul class="space-y-2 text-sm text-slate-600">
                <li><strong>Summary</strong> — income, expenses, savings, transaction count, savings rate, and daily average for the range.</li>
                <li><strong>Charts</strong> — expense-by-category donut, income-vs-expense trend, and category-by-month comparison. Charts can be regenerated repeatedly without duplicates.</li>
                <li><strong>CSV</strong> — downloads the range as a spreadsheet file.</li>
                <li><strong>PDF</strong> — builds one formatted document (summary, categories, transactions) from the loaded report. If transaction data fails to load, the export aborts with an error instead of a misleading partial PDF.</li>
            </ul>
        </div>
    </section>

    
    <section id="import" class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <?= $helpIcon('M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5', 'sky') ?>
            <h2 class="text-lg font-bold text-slate-900">CSV Import</h2>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 space-y-3">
            <p class="text-sm text-slate-600">Bulk-import transactions from a CSV file (max 5 MB, 5000 rows). Columns in order: <code class="bg-slate-100 px-1 rounded text-xs">date, type, category, amount, description</code>.</p>
            <ul class="space-y-2 text-sm text-slate-600">
                <li><strong>Sample file</strong> — press Download Sample CSV to get a ready-made file using real categories; whatever you download is exactly what the importer accepts.</li>
                <li><strong>Preview first</strong> — every row is validated (date, type, existing category with matching type, positive amount) before anything is saved, and quoted commas work.</li>
                <li><strong>Duplicates</strong> — rows identical to existing transactions are skipped and reported, never imported twice.</li>
                <li><strong>Results</strong> — valid rows are saved; invalid rows are listed with reasons. Only an unexpected mid-import failure rolls everything back.</li>
            </ul>
            <div class="bg-amber-50 rounded-xl p-3 text-xs text-amber-700">
                Note: dates as <code class="bg-white border border-amber-200 px-1 rounded font-mono">YYYY-MM-DD</code> or <code class="bg-white border border-amber-200 px-1 rounded font-mono">DD/MM/YYYY</code>; type must be <code class="bg-white border border-amber-200 px-1 rounded font-mono">income</code> or <code class="bg-white border border-amber-200 px-1 rounded font-mono">expense</code>; categories must already exist.
            </div>
        </div>
    </section>

    
    <section id="notifications" class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <?= $helpIcon('M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0', 'rose') ?>
            <h2 class="text-lg font-bold text-slate-900">Notifications</h2>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 space-y-3">
            <p class="text-sm text-slate-600">The bell shows login alerts, over-budget warnings, and info messages. Tap a notification to mark it read, or use Mark All Read.</p>
            <ul class="space-y-2 text-sm text-slate-600">
                <li><strong>30-message limit</strong> — only your newest 30 notifications are kept; older ones are removed automatically when new ones arrive.</li>
                <li><strong>Login alerts</strong> — a new sign-in from a device creates an alert (at most one every 15 minutes) so you notice unfamiliar access.</li>
            </ul>
        </div>
    </section>

    
    <section id="pwa" class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <?= $helpIcon('M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418', 'indigo') ?>
            <h2 class="text-lg font-bold text-slate-900">PWA / Install</h2>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 space-y-3">
            <p class="text-sm text-slate-600">ExpensePro installs like a native app: use Install App (desktop) or Add to Home Screen (mobile). Static assets are cached for speed; your financial data is always fetched fresh and never cached.</p>
            <ul class="space-y-2 text-sm text-slate-600">
                <li><strong>Updates</strong> — when a new version is ready you will see one banner: Update Now reloads once, Later dismisses it.</li>
                <li><strong>Offline</strong> — without a connection you get a friendly offline page instead of stale numbers.</li>
            </ul>
        </div>
    </section>

    
    <section id="account" class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <?= $helpIcon('M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z', 'slate') ?>
            <h2 class="text-lg font-bold text-slate-900">Account &amp; Security</h2>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 p-4 md:p-5 space-y-3">
            <p class="text-sm text-slate-600">Your Profile page holds your details, monthly summary, and settings. There are no profile photos — your account uses a standard user icon.</p>
            <ul class="space-y-2 text-sm text-slate-600">
                <li><strong>Editing</strong> — changing your name, email, or budget requires your current password.</li>
                <li><strong>Passwords</strong> — stored as bcrypt hashes. Changing your password signs out remembered devices automatically.</li>
                <li><strong>Sessions</strong> — tick Remember me only on private devices. Logging out invalidates the session and the remember token together.</li>
            </ul>
        </div>
    </section>

    
    <section id="faq" class="mb-10">
        <div class="flex items-center gap-3 mb-4">
            <?= $helpIcon('M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z', 'slate') ?>
            <h2 class="text-lg font-bold text-slate-900">FAQ</h2>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 divide-y divide-slate-50">
            <?php
            $faqs = [
                ['q' => 'Can I use ExpensePro offline?', 'a' => 'Static assets are cached, but pages and transaction data need a connection: offline navigations show a friendly offline page instead of stale data.'],
                ['q' => 'Is my data secure?', 'a' => 'Yes. Passwords are hashed with bcrypt. Mutating API requests require CSRF tokens. Sessions use HttpOnly, SameSite cookies. Every record is scoped to your account.'],
                ['q' => 'Can I have multiple categories with the same name?', 'a' => 'No. Category names must be unique within their type (income or expense); the same name can exist once as income and once as expense.'],
                ['q' => 'What happens if I delete a category that has transactions?', 'a' => 'The category cannot be deleted while it has transactions. From the delete dialog you can move those transactions to another category of the same type, or edit/delete them first.'],
                ['q' => 'What currencies are supported?', 'a' => 'Currently ExpensePro displays amounts in Indian Rupees (₹). Multi-currency support is planned for a future version.'],
                ['q' => 'How do I change my password?', 'a' => 'Go to your Profile page, open Edit, and use the Change Password fields. You will need your current password; changing it signs out your other remembered devices.'],
                ['q' => 'Why do I see at most 30 notifications?', 'a' => 'By design only your newest 30 notifications are kept, so the list stays relevant and fast.'],
            ];
            foreach ($faqs as $faq):
            ?>
            <details class="group p-5">
                <summary class="flex items-center justify-between cursor-pointer text-sm font-medium text-slate-800 list-none">
                    <?= htmlspecialchars($faq['q'], ENT_QUOTES, 'UTF-8') ?>
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0 group-open:rotate-180 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </summary>
                <p class="mt-3 text-sm text-slate-500"><?= htmlspecialchars($faq['a'], ENT_QUOTES, 'UTF-8') ?></p>
            </details>
            <?php endforeach; ?>
        </div>
    </section>

    
    <div class="text-center text-xs text-slate-400 mb-8">
        ExpensePro v<?= htmlspecialchars(APP_VERSION, ENT_QUOTES, 'UTF-8') ?> &mdash; Built for college project evaluation
    </div>

</main>
