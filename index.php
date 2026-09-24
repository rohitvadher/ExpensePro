<?php

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Cache-Control: no-store, no-cache, must-revalidate');

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/backend/support/Logger.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

set_exception_handler(function (Throwable $e): void {
    if (function_exists('epLogException')) {
        epLogException($e, 'ExpensePro page failed');
    } else {
        error_log('ExpensePro page failed: ' . $e->getMessage());
    }
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Something went wrong — ExpensePro</title></head><body style="font-family:system-ui,sans-serif;background:#f8fafc;color:#334155;display:flex;min-height:100vh;min-height:100dvh;align-items:center;justify-content:center;margin:0;padding:24px;"><main style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:32px;max-width:420px;text-align:center;"><h1 style="color:#0f172a;">Something went wrong</h1><p>ExpensePro could not load this page. Please try again in a moment.</p><p><a style="color:#4f46e5;" href="' . htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') . '">Go to ExpensePro</a></p></main></body></html>';
    exit;
});

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
header('Pragma: no-cache');
header('Expires: 0');

if (isset($_SERVER['HTTP_CACHE_CONTROL']) && strpos($_SERVER['HTTP_CACHE_CONTROL'], 'no-cache') !== false) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, private');
}

$rawPage = $_GET['page'] ?? 'home';
$page = is_string($rawPage) ? trim($rawPage) : '';

$public_pages = ['home', 'login', 'register'];
$auth_pages   = [
    'dashboard',
    'transactions',
    'categories',
    'analytics',
    'profile',
    'budgets',
    'import',
    'reports',
    'notifications',
    'help'
];
$all_pages = array_merge($public_pages, $auth_pages);


$pageTitles = [
    'home' => 'Take control of your money',
    'login' => 'Sign In',
    'register' => 'Create Account',
    'dashboard' => 'Dashboard',
    'transactions' => 'Transactions',
    'categories' => 'Categories',
    'analytics' => 'Analytics',
    'profile' => 'Profile',
    'budgets' => 'Budgets',
    'import' => 'Import Transactions',
    'reports' => 'Reports',
    'notifications' => 'Notifications',
    'help' => 'Help & Guide',
];
if (!isset($pageTitle)) {
    $pageTitle = $pageTitles[$page] ?? 'ExpensePro';
}

if (in_array($page, $public_pages, true)) {

    if (isAuthenticated()) {
        header('Location: ' . BASE_URL . '?page=dashboard');
        exit;
    }

    $pageFile = __DIR__ . '/pages/' . $page . '.php';
    if ($page === 'home' || $page === 'login' || $page === 'register') {
        if (file_exists($pageFile)) {
            require_once __DIR__ . '/layouts/guest-header.php';
            require_once $pageFile;
            require_once __DIR__ . '/layouts/footer.php';
        } else {
            require_once __DIR__ . '/pages/404.php';
        }
    } else {
        require_once __DIR__ . '/pages/404.php';
    }
    exit;
}

if (in_array($page, $auth_pages, true)) {
    requireAuth();

    $pageFile = __DIR__ . '/pages/' . $page . '.php';
    if (file_exists($pageFile)) {
        require_once __DIR__ . '/layouts/app-header.php';
        require_once __DIR__ . '/layouts/sidebar.php';
        require_once $pageFile;
        require_once __DIR__ . '/layouts/mobile-bottom-nav.php';
        require_once __DIR__ . '/layouts/footer.php';
    } else {
        require_once __DIR__ . '/pages/404.php';
    }
    exit;
}

http_response_code(404);
require_once __DIR__ . '/layouts/guest-header.php';
require_once __DIR__ . '/pages/404.php';
require_once __DIR__ . '/layouts/footer.php';
exit;
