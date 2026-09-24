<?php

$pageTitle = 'Create Account';
$error = '';
$success = '';
$pageScripts[] = 'pages/register.js';
?>

<div class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

        
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8">

            
            <div class="text-center mb-8">
                <img src="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>assets/icons/web/apple-touch-icon.png" alt="ExpensePro" class="w-14 h-14 rounded-2xl object-contain mx-auto mb-4 shadow-lg shadow-indigo-600/20">
                <h1 class="text-2xl font-bold text-slate-900">Create Account</h1>
                <p class="text-slate-500 text-sm mt-1">Start tracking your income and expenses</p>
            </div>

            
            <form id="register-form" class="space-y-4" autocomplete="off">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all"
                        placeholder="John Doe"
                        required
                        autocomplete="name"
                    >
                </div>

                
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all"
                        placeholder="you@example.com"
                        required
                        autocomplete="email"
                    >
                </div>

                
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all"
                        placeholder="Min. 8 characters, letters & numbers"
                        required
                        autocomplete="new-password"
                    >
                    <p id="password-hint" class="text-xs text-slate-400 mt-1.5">Must be at least 8 characters with letters and numbers.</p>
                </div>

                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-slate-700 mb-1.5">Confirm Password</label>
                    <input
                        type="password"
                        id="confirm_password"
                        name="confirm_password"
                        class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all"
                        placeholder="Re-enter your password"
                        required
                        autocomplete="new-password"
                    >
                </div>

                
                <div id="register-error" class="hidden bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>

                
                <p class="text-xs text-slate-400 leading-relaxed">
                    By creating an account, you agree to our Terms of Service and Privacy Policy.
                    Default categories will be created for your account.
                </p>

                
                <button
                    type="submit"
                    id="register-btn"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-xl transition-all duration-200 shadow-md shadow-indigo-600/20 hover:shadow-lg hover:shadow-indigo-600/30 active:scale-95 sm:hover:scale-100"
                >
                    Create Account
                </button>

                
                <p class="text-center text-sm text-slate-500">
                    Already have an account?
                    <a href="<?= htmlspecialchars(BASE_URL . '?page=login', ENT_QUOTES, 'UTF-8') ?>" class="text-indigo-600 hover:text-indigo-700 font-medium">Sign in</a>
                </p>
            </form>
        </div>
    </div>
</div>
