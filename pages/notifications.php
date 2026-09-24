<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Notifications';
$pageScripts[] = 'pages/notifications.js';
$notifications = [];
$error = '';
$success = '';
?>

<main class="flex-1 p-4 md:p-6 lg:p-8 max-w-3xl mx-auto w-full">

    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Notifications</h1>
            <p class="text-sm text-slate-500 mt-1">Alerts, reminders, and budget warnings</p>
        </div>
        <button type="button" data-ep-action="markAllRead"
                class="inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-xl transition-all">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
            Mark All Read
        </button>
    </div>

    
    <div id="notifications-container" class="space-y-3">
        <div class="text-center py-12 text-slate-400">
            <img src="<?= BASE_URL ?>assets/icons/SVG/3-dots-fade.svg" alt="Loading" width="40" height="40" class="mx-auto mb-3">
            <p class="text-sm">Loading notifications...</p>
        </div>
    </div>

</main>
