<?php

$currentPage = $_GET['page'] ?? 'dashboard';
?>


<aside class="hidden md:flex md:flex-col fixed top-0 left-0 bottom-0 w-[260px] bg-slate-900 ep-layer-sidebar">

    
    <div class="h-16 flex items-center gap-3 px-6 border-b border-slate-800">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/30 overflow-hidden">
            <img src="<?= BASE_URL ?>assets/icons/web/apple-touch-icon.png" alt="ExpensePro" class="w-9 h-9 object-contain">
        </div>
        <div>
            <span class="font-bold text-white text-base">ExpensePro</span>
            <span class="block text-[10px] text-slate-400 font-medium uppercase tracking-wider">Personal Finance</span>
        </div>
    </div>

    
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

        <?php $isActive = fn($page) => $currentPage === $page; ?>

        
        <a href="<?= BASE_URL ?>?page=dashboard"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                  <?= $currentPage === 'dashboard' ? 'bg-indigo-600/20 text-indigo-400 border-l-[3px] border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-l-[3px] border-transparent' ?>">
            <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
            </svg>
            <span>Dashboard</span>
        </a>

        
        <a href="<?= BASE_URL ?>?page=transactions"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                  <?= $currentPage === 'transactions' ? 'bg-indigo-600/20 text-indigo-400 border-l-[3px] border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-l-[3px] border-transparent' ?>">
            <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
            </svg>
            <span>Transactions</span>
        </a>

        
        <div class="border-t border-slate-800 my-3"></div>

        
        <a href="<?= BASE_URL ?>?page=analytics"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                  <?= $isActive('analytics') ? 'bg-indigo-600/20 text-indigo-400 border-l-[3px] border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-l-[3px] border-transparent' ?>">
            <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
            </svg>
            <span>Analytics</span>
        </a>

        
        <a href="<?= BASE_URL ?>?page=categories"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                  <?= $isActive('categories') ? 'bg-indigo-600/20 text-indigo-400 border-l-[3px] border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-l-[3px] border-transparent' ?>">
            <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
            </svg>
            <span>Categories</span>
        </a>

        
        <a href="<?= BASE_URL ?>?page=budgets"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                  <?= $isActive('budgets') ? 'bg-indigo-600/20 text-indigo-400 border-l-[3px] border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-l-[3px] border-transparent' ?>">
            <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"/>
            </svg>
            <span>Budgets</span>
        </a>

        
        <a href="<?= BASE_URL ?>?page=import"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                  <?= $isActive('import') ? 'bg-indigo-600/20 text-indigo-400 border-l-[3px] border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-l-[3px] border-transparent' ?>">
            <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
            </svg>
            <span>Import</span>
        </a>

        
        <a href="<?= BASE_URL ?>?page=reports"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                  <?= $isActive('reports') ? 'bg-indigo-600/20 text-indigo-400 border-l-[3px] border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-l-[3px] border-transparent' ?>">
            <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            <span>Reports</span>
        </a>

        
        <div class="border-t border-slate-800 my-3"></div>

        
        <a href="<?= BASE_URL ?>?page=profile"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                  <?= $isActive('profile') ? 'bg-indigo-600/20 text-indigo-400 border-l-[3px] border-indigo-500' : 'text-slate-300 hover:bg-slate-800 hover:text-white border-l-[3px] border-transparent' ?>">
            <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
            </svg>
            <span>Profile</span>
        </a>

    </nav>

    
    <div class="px-3 py-4 border-t border-slate-800">
        <div class="flex items-center gap-3 px-4">
            <?= userIcon(8) ?>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-200 truncate"><?= htmlspecialchars($currentUser['name'] ?? 'User') ?></p>
                <p class="text-xs text-slate-500 truncate"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
            </div>
        </div>
    </div>
</aside>


<div id="main-content-wrapper" class="md:ml-[260px] flex-1 flex flex-col pb-20 md:pb-0">
    
    <div class="hidden md:block h-16"></div>
