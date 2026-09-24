<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Page Not Found';
$isLoggedIn = false;

if (function_exists('isAuthenticated')) {
    $isLoggedIn = isAuthenticated();
}
?>

<div class="flex-1 flex items-center justify-center px-4 py-16">
    <div class="text-center max-w-md">

        
        <div class="text-8xl font-extrabold bg-gradient-to-br from-indigo-500 to-indigo-700 bg-clip-text text-transparent mb-4">
            404
        </div>

        
        <h1 class="text-2xl font-bold text-slate-900 mb-2">Page Not Found</h1>
        <p class="text-slate-500 text-sm leading-relaxed mb-8">
            The page you're looking for doesn't exist or has been moved.
            Please check the URL or navigate to a different page.
        </p>

        
        <a href="<?= htmlspecialchars(BASE_URL . ($isLoggedIn ? '?page=dashboard' : ''), ENT_QUOTES, 'UTF-8') ?>"
           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-xl transition-all duration-200 shadow-md shadow-indigo-600/20">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75"/>
            </svg>
            <?= htmlspecialchars($isLoggedIn ? 'Go to Dashboard' : 'Go to Home', ENT_QUOTES, 'UTF-8') ?>
        </a>
    </div>
</div>
