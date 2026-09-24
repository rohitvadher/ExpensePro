<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — ' . APP_NAME : APP_NAME ?></title>
    <meta name="description" content="ExpensePro - Track your income and expenses">
    <meta name="theme-color" content="#6366F1">
    <meta name="csrf-token" content="<?= csrfToken() ?>">

    
    <link rel="icon" href="<?= BASE_URL ?>assets/icons/web/favicon.ico" sizes="48x48">
    <link rel="icon" href="<?= BASE_URL ?>assets/icons/web/icon-192.png" sizes="192x192" type="image/png">
    <link rel="icon" href="<?= BASE_URL ?>assets/icons/web/icon-512.png" sizes="512x512" type="image/png">
    <link rel="icon" href="<?= BASE_URL ?>assets/icons/web/icon-192-maskable.png" sizes="192x192" type="image/png" purpose="maskable">
    <link rel="icon" href="<?= BASE_URL ?>assets/icons/web/icon-512-maskable.png" sizes="512x512" type="image/png" purpose="maskable">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>assets/icons/web/apple-touch-icon.png" sizes="180x180">
    <link rel="shortcut icon" href="<?= BASE_URL ?>assets/icons/web/favicon.ico">

    
    <link rel="manifest" href="<?= BASE_URL ?>manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ExpensePro">
    <meta name="mobile-web-app-capable" content="yes">

    
    <link rel="stylesheet" href="<?= assetUrl('assets/css/tailwind.css') ?>">

    
    <link rel="stylesheet" href="<?= assetUrl('assets/css/core/tokens.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/core/base.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/core/utilities.css') ?>">

    
    <link rel="stylesheet" href="<?= assetUrl('assets/css/layout/layers.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/layout/safe-area.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/layout/responsive.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/layout/print.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/layout/desktop.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/layout/mobile.css') ?>">

    
    <link rel="stylesheet" href="<?= assetUrl('assets/css/components/buttons.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/components/forms.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/components/cards.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/components/modal.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/components/toast.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/components/tables.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/components/states.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/components/progress.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/components/file-drop.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/components/loaders.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/components/empty-states.css') ?>">

    
    <link rel="stylesheet" href="<?= assetUrl('assets/css/features/pdf.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/features/datepicker.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('assets/css/features/pwa.css') ?>">

    
    <link rel="stylesheet" href="<?= assetUrl('assets/css/pages/home.css') ?>">

    
    <script>
        var BASE_URL = '<?= BASE_URL ?>';
        var EP_BUILD = '<?= appBuild() ?>';
    </script>
</head>
<body class="bg-slate-50 text-slate-700 antialiased min-h-screen">

    <a href="#ep-main" class="ep-skip-link">Skip to main content</a>

    <?php

    $currentUser = getCurrentUser();
    $currentPage = $_GET['page'] ?? 'dashboard';
    ?>

    
    <nav class="hidden md:flex fixed top-0 left-0 right-0 h-16 bg-white border-b border-slate-200 ep-layer-header items-center px-6" style="margin-left: 260px;">
        <div class="flex items-center justify-between w-full">
            
            <div>
                <h1 class="text-lg font-semibold text-slate-900">
                    <?= $pageTitle ?? 'Dashboard' ?>
                </h1>
            </div>

            
            <div class="flex items-center gap-3">
                
                <a href="<?= BASE_URL ?>?page=notifications"
                   class="relative p-2 rounded-lg text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-colors"
                   id="notification-bell-btn"
                   title="Notifications"
                   aria-label="Notifications">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <span id="notification-badge" class="hidden absolute top-1 right-1 w-4 h-4 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none"></span>
                </a>

                
                <button type="button" id="install-btn" class="hidden text-xs bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg font-medium hover:bg-indigo-100 transition-colors" data-ep-action="handleInstallClick">
                    + Install App
                </button>

                
                <div class="relative">
                    <button type="button" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-slate-100 transition-colors" id="profile-menu-btn" aria-label="Account menu">
                        <?= userIcon(8) ?>
                        <span class="text-sm font-medium text-slate-700 hidden lg:inline">
                            <?= htmlspecialchars($currentUser['name'] ?? 'User') ?>
                        </span>
                        <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>

                    
                    <div class="hidden absolute right-0 top-full mt-2 z-50 bg-white divide-y divide-slate-100 rounded-xl shadow-xl border border-slate-200 w-64 max-h-[80vh] overflow-y-auto" id="profile-dropdown" role="menu" aria-label="Account">
                        <div class="px-4 py-3 flex items-center gap-3">
                            <?= userIcon(10) ?>
                            <div>
                                <p class="text-sm font-medium text-slate-900"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></p>
                                <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                            </div>
                        </div>
                        <ul class="py-1 text-sm text-slate-700">
                            <li>
                                <a href="<?= BASE_URL ?>?page=profile" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                    Profile
                                </a>
                            </li>
                            <li>
                                <a href="<?= BASE_URL ?>?page=help" class="flex items-center gap-3 px-4 py-2.5 hover:bg-slate-50 transition-colors">
                                    <svg class="w-4 h-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                                    Help &amp; Guide
                                </a>
                            </li>
                        </ul>
                        <div class="py-1">
                            <button type="button" data-ep-action="handleLogout" class="flex items-center gap-3 w-full text-left px-4 py-2.5 text-sm text-rose-600 hover:bg-rose-50 transition-colors">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    
    <header class="md:hidden fixed top-0 left-0 right-0 h-14 bg-white border-b border-slate-200 ep-layer-header flex items-center justify-between px-4 ep-header-safe">
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>?page=dashboard" class="flex items-center gap-2">
                <img src="<?= BASE_URL ?>assets/icons/web/apple-touch-icon.png" alt="ExpensePro" class="w-8 h-8 rounded-lg object-contain">
                <span class="font-semibold text-slate-900">ExpensePro</span>
            </a>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= BASE_URL ?>?page=notifications" class="relative p-1.5 text-slate-500 hover:text-slate-700" aria-label="Notifications">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                </svg>
                <span id="notification-badge-mobile" class="hidden absolute top-0 right-0 w-4 h-4 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none"></span>
            </a>
            <a href="<?= BASE_URL ?>?page=profile">
                <?= userIcon(8) ?>
            </a>
        </div>
    </header>

    
    <div class="flex min-h-screen pt-14 md:pt-0">
