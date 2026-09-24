<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Profile';
$pageScripts[] = 'pages/profile.js';
$currentUser = null;
$error = '';
$success = '';

if (function_exists('getCurrentUser')) {
    $currentUser = getCurrentUser();
}

if (!$currentUser):
?>

<main class="flex-1 p-4 md:p-6 lg:p-8 max-w-3xl mx-auto w-full">
    <div class="bg-white rounded-xl border border-rose-200 p-8 text-center">
        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-rose-100 flex items-center justify-center">
            <svg class="w-8 h-8 text-rose-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
        </div>
        <h1 class="text-lg font-bold text-slate-900 mb-2">Session Expired</h1>
        <p class="text-sm text-slate-500 mb-6">Your session has expired or the user account was not found. Please log in again.</p>
        <a href="<?= htmlspecialchars(BASE_URL . '?page=login', ENT_QUOTES, 'UTF-8') ?>" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-all shadow-md shadow-indigo-600/20">
            Go to Login
        </a>
    </div>
</main>

<?php else: ?>

<main class="flex-1 p-4 md:p-6 lg:p-8 max-w-3xl mx-auto w-full">

    
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Profile</h1>
        <p class="text-sm text-slate-500 mt-1">Your account information and statistics</p>
    </div>

    
    <div class="bg-white rounded-xl border border-slate-100 overflow-hidden mb-6">

        
        <div class="h-32 bg-gradient-to-r from-indigo-600 to-indigo-400 relative">
            <div class="absolute -bottom-12 left-6">
                <div class="w-24 h-24 rounded-2xl border-4 border-white shadow-lg overflow-hidden bg-indigo-100 flex items-center justify-center">
                    <?= userIcon(24) ?>
                </div>
            </div>
        </div>

        
        <div class="pt-16 px-6 pb-6">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <h2 id="profile-name-display" class="text-xl font-bold text-slate-900"><?= htmlspecialchars($currentUser['name'] ?? 'User', ENT_QUOTES, 'UTF-8') ?></h2>
                    <p id="profile-email-display" class="text-sm text-slate-500"><?= htmlspecialchars($currentUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-xs text-slate-400 mt-1">
                        Member since <?= htmlspecialchars(date('j M Y', strtotime($currentUser['member_since'] ?? 'now')), ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <button type="button" data-ep-action="toggleProfileEdit"
                            class="inline-flex items-center gap-2 bg-white border border-indigo-200 text-indigo-600 hover:bg-indigo-50 text-sm font-medium px-4 py-2 rounded-xl transition-all">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                        Edit
                    </button>
                    <button type="button" data-ep-action="handleLogout"
                            class="inline-flex items-center gap-2 bg-white border border-rose-200 text-rose-600 hover:bg-rose-50 text-sm font-medium px-4 py-2 rounded-xl transition-all">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                        Logout
                    </button>
                </div>
            </div>

            
            <div id="profile-stats" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="col-span-full text-center py-8 text-slate-400 text-sm"><img src="<?= BASE_URL ?>assets/icons/SVG/3-dots-fade.svg" alt="Loading" width="36" height="36" class="mx-auto mb-2">Loading stats...</div>
            </div>
        </div>
    </div>

    
    <div id="profile-edit-section" class="hidden bg-white rounded-xl border border-slate-100 p-4 md:p-5 mb-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900">Edit Profile</h3>
            <button type="button" data-ep-action="toggleProfileEdit" aria-label="Close editor" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="profile-form-error" class="hidden bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm mb-4"></div>
        <div id="profile-form-success" class="hidden bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm mb-4"></div>

        <form id="profile-edit-form" class="space-y-4">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

            
            <div>
                <label for="edit-name" class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                <input type="text" id="edit-name" name="name"
                       value="<?= htmlspecialchars($currentUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
            </div>

            
            <div>
                <label for="edit-email" class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                <input type="email" id="edit-email" name="email"
                       value="<?= htmlspecialchars($currentUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
            </div>

            
            <div>
                <label for="edit-monthly-budget" class="block text-sm font-medium text-slate-700 mb-1.5">Monthly Budget (optional)</label>
                <input type="number" id="edit-monthly-budget" name="monthly_budget" step="0.01" min="0"
                       placeholder="e.g., 50000"
                       value="<?= htmlspecialchars($currentUser['monthly_budget'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                <p class="text-xs text-slate-400 mt-1">Set a monthly spending limit to track your budget on the dashboard.</p>
            </div>

            <hr class="border-slate-100">

            
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Change Password (optional)</p>

            
            <div>
                <label for="edit-current-password" class="block text-sm font-medium text-slate-700 mb-1.5">Current Password</label>
                <input type="password" id="edit-current-password" name="current_password"
                       placeholder="Enter current password to confirm"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                <p class="text-xs text-slate-400 mt-1">Required to verify your identity before saving changes.</p>
            </div>

            
            <div>
                <label for="edit-new-password" class="block text-sm font-medium text-slate-700 mb-1.5">New Password (optional)</label>
                <input type="password" id="edit-new-password" name="new_password"
                       placeholder="Leave blank to keep current password"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                <p class="text-xs text-slate-400 mt-1">At least 8 characters with 1 letter and 1 number.</p>
            </div>

            
            <div>
                <label for="edit-confirm-password" class="block text-sm font-medium text-slate-700 mb-1.5">Confirm New Password</label>
                <input type="password" id="edit-confirm-password" name="confirm_password"
                       placeholder="Re-enter new password"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
            </div>

            
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" id="profile-save-btn"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-xl transition-all duration-200 shadow-md shadow-indigo-600/20 hover:shadow-lg active:scale-95 sm:hover:scale-100">
                    Save Changes
                </button>
                <button type="button" data-ep-action="toggleProfileEdit"
                        class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                    Cancel
                </button>
            </div>
        </form>
    </div>

</main>

<?php endif; ?>
