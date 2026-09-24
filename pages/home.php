<?php

$pageTitle = 'Take control of your money';
$pageScripts = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — <?= APP_NAME ?></title>
    <meta name="description" content="ExpensePro - Track your income and expenses, manage budgets, and understand your spending.">
    <meta name="theme-color" content="#6366F1">
    <link rel="icon" href="<?= BASE_URL ?>assets/icons/web/favicon.ico" sizes="48x48">
    <link rel="icon" href="<?= BASE_URL ?>assets/icons/web/icon-192.png" sizes="192x192" type="image/png">
    <link rel="icon" href="<?= BASE_URL ?>assets/icons/web/icon-512.png" sizes="512x512" type="image/png">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/icons/web/apple-touch-icon.png" sizes="180x180">
    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/icons/web/favicon.ico">
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ExpensePro">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/tailwind.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/core/tokens.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/core/base.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/pages/home.css') ?>">
    <script>var BASE_URL = '<?= BASE_URL ?>';</script>
</head>
<body class="bg-white text-slate-700 antialiased">

    <nav class="ep-nav">
        <div class="ep-nav-inner">
            <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>" class="ep-nav-brand">
                <img src="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>assets/icons/web/apple-touch-icon.png" alt="" class="ep-nav-logo">
                <span class="ep-nav-name">ExpensePro</span>
            </a>
            <div class="ep-nav-links">
                <a href="<?= htmlspecialchars(BASE_URL . '?page=login', ENT_QUOTES, 'UTF-8') ?>" class="ep-nav-link">Log in</a>
                <a href="<?= htmlspecialchars(BASE_URL . '?page=register', ENT_QUOTES, 'UTF-8') ?>" class="ep-nav-cta">Get Started</a>
            </div>
        </div>
    </nav>

    <main>
        <section class="ep-hero">
            <div class="ep-hero-inner">
                <span class="ep-badge">Personal Finance Manager</span>
                <h1 class="ep-hero-title">Expense management,<br>made simple.</h1>
                <p class="ep-hero-desc">
                    Track every rupee. Set budgets. See where your money goes.
                    ExpensePro gives you a clear picture of your finances — free, private, and easy to use.
                </p>
                <div class="ep-hero-actions">
                    <a href="<?= htmlspecialchars(BASE_URL . '?page=register', ENT_QUOTES, 'UTF-8') ?>" class="ep-btn ep-btn-primary">Get Started Free</a>
                    <a href="<?= htmlspecialchars(BASE_URL . '?page=login', ENT_QUOTES, 'UTF-8') ?>" class="ep-btn ep-btn-outline">Log In</a>
                </div>
                <div class="ep-hero-proof">
                    <span class="ep-proof-dot"></span>
                    Free for personal use
                    <span class="ep-proof-sep"></span>
                    Mobile-friendly
                    <span class="ep-proof-sep"></span>
                    Private per-user data
                </div>
            </div>
        </section>

        <section class="ep-section" id="features">
            <div class="ep-section-inner">
                <span class="ep-section-label">Features</span>
                <h2 class="ep-section-title">Everything you need to manage money</h2>
                <p class="ep-section-desc">A clean, focused set of tools for daily expense tracking and monthly reviews.</p>
                <div class="ep-features">
                    <article class="ep-feature">
                        <div class="ep-feature-icon" style="background:#eef2ff;color:#4f46e5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                        </div>
                        <h3>Dashboard</h3>
                        <p>Current-month income, expenses, balance, and budget status at a glance.</p>
                    </article>
                    <article class="ep-feature">
                        <div class="ep-feature-icon" style="background:#f0fdf4;color:#16a34a">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        </div>
                        <h3>Transactions</h3>
                        <p>Add income and expenses with search, filters, and pagination.</p>
                    </article>
                    <article class="ep-feature">
                        <div class="ep-feature-icon" style="background:#fef3c7;color:#d97706">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
                        </div>
                        <h3>Categories</h3>
                        <p>Organize spending with custom income and expense categories.</p>
                    </article>
                    <article class="ep-feature">
                        <div class="ep-feature-icon" style="background:#fce7f3;color:#db2777">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4z"/></svg>
                        </div>
                        <h3>Budgets</h3>
                        <p>Set weekly, monthly, or yearly limits and get alerts before overspending.</p>
                    </article>
                    <article class="ep-feature">
                        <div class="ep-feature-icon" style="background:#ede9fe;color:#7c3aed">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                        </div>
                        <h3>Analytics</h3>
                        <p>Twelve-month trends and category breakdowns from your transaction data.</p>
                    </article>
                    <article class="ep-feature">
                        <div class="ep-feature-icon" style="background:#ecfdf5;color:#059669">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                        </div>
                        <h3>Reports</h3>
                        <p>Date-range summaries with CSV and printable PDF export.</p>
                    </article>
                    <article class="ep-feature">
                        <div class="ep-feature-icon" style="background:#fff7ed;color:#ea580c">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                        </div>
                        <h3>CSV Import</h3>
                        <p>Bulk-import transactions from CSV files with validation and duplicate detection.</p>
                    </article>
                    <article class="ep-feature">
                        <div class="ep-feature-icon" style="background:#f0f9ff;color:#0284c7">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        </div>
                        <h3>Notifications</h3>
                        <p>Budget alerts and login notifications keep you informed.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="ep-section ep-section-gray" id="how-it-works">
            <div class="ep-section-inner">
                <span class="ep-section-label">How It Works</span>
                <h2 class="ep-section-title">Start in three steps</h2>
                <p class="ep-section-desc">No complex setup. Create an account and begin tracking immediately.</p>
                <div class="ep-steps">
                    <div class="ep-step">
                        <div class="ep-step-num">1</div>
                        <h3>Create your account</h3>
                        <p>Sign up for free. Default income and expense categories are added automatically.</p>
                    </div>
                    <div class="ep-step">
                        <div class="ep-step-num">2</div>
                        <h3>Add your transactions</h3>
                        <p>Record income and expenses as they happen. Use templates for repeat entries.</p>
                    </div>
                    <div class="ep-step">
                        <div class="ep-step-num">3</div>
                        <h3>Understand your finances</h3>
                        <p>Review budgets, analytics, and reports to see where your money goes.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="ep-section ep-cta-section">
            <div class="ep-section-inner ep-cta-box">
                <h2 class="ep-cta-title">Start managing your expenses</h2>
                <p class="ep-cta-desc">Create a free account and take control of your finances today.</p>
                <div class="ep-cta-actions">
                    <a href="<?= htmlspecialchars(BASE_URL . '?page=register', ENT_QUOTES, 'UTF-8') ?>" class="ep-btn ep-btn-primary">Create Account</a>
                    <a href="<?= htmlspecialchars(BASE_URL . '?page=login', ENT_QUOTES, 'UTF-8') ?>" class="ep-btn ep-btn-outline">Log In</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="ep-footer">
        <div class="ep-footer-inner">
            <div class="ep-footer-brand">
                <img src="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>assets/icons/web/apple-touch-icon.png" alt="" class="ep-footer-logo">
                <span>ExpensePro</span>
            </div>
            <p class="ep-footer-copy">Personal Expense Management. Built for learning purposes.</p>
        </div>
    </footer>

</body>
</html>
