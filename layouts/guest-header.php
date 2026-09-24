<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — ' . APP_NAME : APP_NAME ?></title>
    <meta name="description" content="ExpensePro - Track your income and expenses beautifully">
    <meta name="theme-color" content="#1E293B">
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
</head>
<body class="bg-slate-50 text-slate-700 antialiased min-h-screen flex flex-col">

    <a href="#ep-main" class="ep-skip-link">Skip to main content</a>

    
    <script>
        var BASE_URL = '<?= BASE_URL ?>';
        var EP_BUILD = '<?= appBuild() ?>';
    </script>

    
    <div class="flex min-h-screen">
        <div class="flex-1 flex flex-col">
