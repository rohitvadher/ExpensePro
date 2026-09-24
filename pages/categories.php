<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = 'Categories';
$pageScripts = ['categories.js'];
$categories = [];
$error = '';
$success = '';
?>

<main class="flex-1 p-4 md:p-6 lg:p-8 max-w-7xl mx-auto w-full">

    
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Categories</h1>
            <p class="text-sm text-slate-500 mt-1">Organize your income and expense types</p>
        </div>
        <button type="button" data-ep-action="openCreateCategoryModal"
                class="hidden md:inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-all shadow-md shadow-indigo-600/20 hover:shadow-lg active:scale-95 sm:hover:scale-100">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Category
        </button>
    </div>

    
    <div class="flex items-center justify-between mb-6">
        <div class="flex gap-2">
            <button type="button" class="category-tab bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm font-medium transition-all"
                    data-type="income">
                Income
            </button>
            <button type="button" class="category-tab bg-slate-100 text-slate-600 px-5 py-2 rounded-lg text-sm font-medium transition-all"
                    data-type="expense">
                Expense
            </button>
        </div>
        <button type="button" data-ep-action="openCreateCategoryModal"
                class="md:hidden inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-3 py-2 rounded-lg transition-all shadow-md shadow-indigo-600/20 active:scale-95">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add
        </button>
    </div>

    
    <div id="categories-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        
        <div class="col-span-full text-center py-12 text-slate-400 text-sm"><img src="<?= BASE_URL ?>assets/icons/SVG/3-dots-fade.svg" alt="Loading" width="36" height="36" class="mx-auto mb-2">Loading categories...</div>
    </div>

    
    <div id="category-modal" class="hidden fixed inset-0 ep-layer-modal">
        <div class="absolute inset-0 bg-white/80 backdrop-blur-md modal-backdrop" data-ep-action="closeCategoryModal"></div>
        <div class="modal-shell">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md modal-content modal-panel">
                <div class="p-5 md:p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 id="category-modal-title" class="text-lg font-bold text-slate-900">Add Category</h2>
                        <button type="button" data-ep-action="closeCategoryModal" aria-label="Close dialog" class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div id="cat-form-error" class="hidden bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm mb-4"></div>

                    <form id="category-form" class="space-y-4" autocomplete="off">
                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" id="cat-edit-id" name="edit_id" value="">
                        <input type="hidden" id="cat-type" name="type" value="income">

                        <div>
                            <label for="cat-name" class="block text-sm font-medium text-slate-700 mb-1.5">Category Name</label>
                            <input type="text" id="cat-name" name="name" required
                                   placeholder="e.g., Freelancing, Groceries"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 placeholder-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                        </div>

                        <div>
                            <label for="cat-icon" class="block text-sm font-medium text-slate-700 mb-1.5">Icon</label>
                            <select id="cat-icon" name="icon"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all">
                                <option value="tag">Tag</option>
                                <option value="briefcase">Briefcase</option>
                                <option value="shopping-bag">Shopping Bag</option>
                                <option value="truck">Truck</option>
                                <option value="heart">Heart</option>
                                <option value="academic-cap">Academic Cap</option>
                                <option value="banknotes">Banknotes</option>
                                <option value="home">Home</option>
                                <option value="sparkles">Sparkles</option>
                                <option value="chart-bar">Chart Bar</option>
                                <option value="cog">Cog</option>
                                <option value="globe-alt">Globe</option>
                            </select>
                        </div>

                        <div>
                            <label for="cat-color" class="block text-sm font-medium text-slate-700 mb-1.5">Color</label>
                            <div class="flex items-center gap-3">
                                <input type="color" id="cat-color" name="color" value="#6366F1"
                                       class="w-10 h-10 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                                <span class="text-xs text-slate-400">Choose a color for this category</span>
                            </div>
                        </div>

                        <button type="submit" id="cat-submit-btn"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 px-4 rounded-xl transition-all duration-200 shadow-md shadow-indigo-600/20 hover:shadow-lg active:scale-95 sm:hover:scale-100">
                            Add Category
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <div id="delete-category-modal" class="hidden fixed inset-0 ep-layer-modal">
        <div class="absolute inset-0 bg-white/80 backdrop-blur-md modal-backdrop" data-ep-action="closeCategoryDeleteModal"></div>
        <div class="modal-shell">
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 modal-content modal-panel">
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-rose-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-rose-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-slate-900 mb-2 text-center">Delete Category</h2>
                <p class="text-sm text-slate-500 mb-2 text-center">Are you sure you want to delete</p>
                <p class="text-sm font-semibold text-slate-900 mb-4 text-center" id="delete-cat-name">---</p>

                <div id="delete-cat-move-section" class="hidden mb-4">
                    <hr class="border-slate-100 mb-4">
                    <p class="text-xs text-slate-500 mb-2">This category has <span id="delete-cat-txn-count">0</span> transaction(s). Move them to another category before deleting:</p>
                    <select id="delete-cat-move-to" class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-slate-50 text-sm text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition-all mb-2">
                        <option value="">Select a category</option>
                    </select>
                </div>

                <div class="flex gap-3">
                    <button type="button" id="cancel-cat-delete-btn"
                            class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        Cancel
                    </button>
                    <button type="button" id="confirm-cat-delete-btn"
                            class="flex-1 px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium transition-all shadow-md active:scale-95">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

</main>
