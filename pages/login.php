<?php

$pageTitle = 'Sign In';
$error = '';
$success = '';
$pageScripts[] = 'pages/login.js';
?>

<div class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

        
        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 p-8">

            
            <div class="text-center mb-8">
                <img src="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>assets/icons/web/apple-touch-icon.png" alt="ExpensePro" class="w-14 h-14 rounded-2xl object-contain mx-auto mb-4 shadow-lg shadow-indigo-600/20">
                <h1 class="text-2xl font-bold text-slate-900">Welcome Back</h1>
                <p class="text-slate-500 text-sm mt-1">Sign in to your ExpensePro account</p>
            </div>

            
            <form id="login-form" class="space-y-5" autocomplete="off">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                
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
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2.5 cursor-pointer py-1">
                        <input
                            type="checkbox"
                            id="remember_me"
                            name="remember_me"
                            class="w-[18px] h-[18px] rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                            checked
                        >
                        <span class="text-sm text-slate-600 select-none">Remember me</span>
                    </label>
                </div>

                
                <div id="login-error" class="hidden bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>

                
                <button
                    type="submit"
                    id="login-btn"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-xl transition-all duration-200 shadow-md shadow-indigo-600/20 hover:shadow-lg hover:shadow-indigo-600/30 active:scale-95 sm:hover:scale-100"
                >
                    Sign In
                </button>

                
                <p class="text-center text-sm text-slate-500 mt-4">
                    Don't have an account?
                    <a href="<?= htmlspecialchars(BASE_URL . '?page=register', ENT_QUOTES, 'UTF-8') ?>" class="text-indigo-600 hover:text-indigo-700 font-medium">Create one</a>
                </p>
            </form>

            
            <div class="mt-6 pt-5 border-t border-slate-100">
                <p class="text-xs text-slate-400 text-center leading-relaxed">
                    Forgot your password? Please contact the developer or administrator to reset your account.
                </p>
            </div>
        </div>
    </div>
</div>
