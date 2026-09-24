<?php

$currentPage = $_GET['page'] ?? 'dashboard';
?>

<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 ep-layer-nav flex items-start justify-around ep-nav-safe px-2" style="padding-top: 8px; height: auto; min-height: 64px;">
    
    
    <a href="<?= BASE_URL ?>?page=dashboard"
        class="flex flex-col items-center gap-0.5 px-2 py-1 min-w-[56px] transition-colors
               <?= $currentPage === 'dashboard' ? 'text-indigo-600' : 'text-slate-500' ?>">
        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="<?= $currentPage === 'dashboard' ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
        </svg>
        <span class="text-[10px] font-medium">Home</span>
    </a>

    
    <a href="<?= BASE_URL ?>?page=transactions"
       class="flex flex-col items-center gap-0.5 px-2 py-1 min-w-[56px] transition-colors
               <?= $currentPage === 'transactions' ? 'text-indigo-600' : 'text-slate-500' ?>">
        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="<?= $currentPage === 'transactions' ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
        </svg>
        <span class="text-[10px] font-medium">Transactions</span>
    </a>

    
    <div class="flex items-center justify-center" style="width: 72px;">
        <?php if ($currentPage === 'transactions'): ?>
            <button type="button" data-ep-action="openCreateModal"
                    class="w-14 h-14 rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 flex items-center justify-center hover:bg-indigo-700 transition-all active:scale-90 -mt-5"
                    aria-label="Add transaction">
                <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </button>
        <?php else: ?>
            <a href="<?= BASE_URL ?>?page=transactions"
               class="w-14 h-14 rounded-full bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 flex items-center justify-center hover:bg-indigo-700 transition-all active:scale-90 -mt-5"
               aria-label="Add transaction">
                <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            </a>
        <?php endif; ?>
    </div>

    
    <a href="<?= BASE_URL ?>?page=categories"
        class="flex flex-col items-center gap-0.5 px-2 py-1 min-w-[56px] transition-colors
               <?= $currentPage === 'categories' ? 'text-indigo-600' : 'text-slate-500' ?>">
        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="<?= $currentPage === 'categories' ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
        </svg>
        <span class="text-[10px] font-medium">Categories</span>
    </a>

    
    <button type="button" data-ep-action="openMoreSheet"
        class="flex flex-col items-center gap-0.5 px-2 py-1 min-w-[56px] transition-colors text-slate-500"
        aria-label="More options">
        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
        </svg>
        <span class="text-[10px] font-medium">More</span>
    </button>

</nav>


<div id="more-sheet" class="hidden fixed inset-0 ep-layer-sheet">
    <div class="absolute inset-0 bg-slate-900/40" data-ep-action="closeMoreSheet" style="animation: modalFadeIn 0.2s ease-out"></div>
    <div class="ep-sheet">
        <div class="ep-sheet-inner">
            <div class="w-10 h-1 rounded-full bg-slate-200 mx-auto mb-3"></div>
            <div class="flex items-center justify-between mb-3 px-1">
                <h2 class="text-base font-bold text-slate-900">More</h2>
                <button type="button" data-ep-action="closeMoreSheet" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors" aria-label="Close menu">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
                <p class="ep-sheet-group-label">Manage</p>
                <div class="grid grid-cols-2 gap-2 mb-4">
                    <?php
                    $moreLinks = [
                        ['analytics', 'Analytics', 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
                        ['budgets', 'Budgets', 'M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6zM13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z'],
                    ];
                    foreach ($moreLinks as $link):
                    ?>
                    <a href="<?= BASE_URL ?>?page=<?= $link[0] ?>"
                       class="flex flex-col items-center gap-1.5 p-3 rounded-xl min-h-[76px] justify-center transition-colors
                              <?= $currentPage === $link[0] ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50' ?>">
                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $link[2] ?>"/>
                        </svg>
                        <span class="text-[11px] font-medium"><?= $link[1] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <p class="ep-sheet-group-label">Account</p>
                <div class="ep-sheet-list">
                    <a href="<?= BASE_URL ?>?page=profile" class="ep-sheet-item <?= $currentPage === 'profile' ? 'ep-sheet-item-active' : '' ?>">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        <span>Profile</span>
                    </a>
                    <a href="<?= BASE_URL ?>?page=notifications" class="ep-sheet-item <?= $currentPage === 'notifications' ? 'ep-sheet-item-active' : '' ?>">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                        <span>Notifications</span>
                    </a>
                    <a href="<?= BASE_URL ?>?page=help" class="ep-sheet-item <?= $currentPage === 'help' ? 'ep-sheet-item-active' : '' ?>">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                        <span>Help &amp; Guide</span>
                    </a>
                    <button type="button" data-ep-action="handleLogout" class="ep-sheet-item ep-sheet-item-danger">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                        <span>Logout</span>
                    </button>
        </div>
    </div>
</div>
</div>
