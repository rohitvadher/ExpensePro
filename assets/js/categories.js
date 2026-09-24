var categoryState = {
    activeType: 'income',
    editingId: null,
    abort: null,
    seq: 0
};

document.addEventListener('DOMContentLoaded', function () {
    try {
        initCategoryTabs();
        initCategoryForm();
        initDeleteConfirmation();
        initCategoryListEvents();
        loadCategories('income');
        initIconPicker();
    } catch (e) {
        var container = document.getElementById('categories-list');
        if (container) {
            container.innerHTML = errorState({ message: 'Something went wrong loading this page.' });
            bindRetry(container, function () { window.location.reload(); });
        }
    }
});

function initCategoryTabs() {
    var tabs = document.querySelectorAll('.category-tab');
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            var type = this.getAttribute('data-type');
            if (!type) return;

            tabs.forEach(function (t) {
                t.classList.remove('bg-indigo-600', 'text-white');
                t.classList.add('bg-slate-100', 'text-slate-600');
            });
            this.classList.remove('bg-slate-100', 'text-slate-600');
            this.classList.add('bg-indigo-600', 'text-white');

            categoryState.activeType = type;
            loadCategories(type);
        });
    });
}

function loadCategories(type) {
    var container = document.getElementById('categories-list');
    if (!container) return;

    
    if (categoryState.abort) {
        try { categoryState.abort.abort(); } catch (e) {}
    }
    categoryState.abort = ('AbortController' in window) ? new AbortController() : null;
    var signal = categoryState.abort ? categoryState.abort.signal : undefined;
    var mySeq = ++categoryState.seq;

    container.innerHTML =
        '<div class="col-span-full flex items-center justify-center py-12">' +
        '<div class="animate-spin w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full"></div>' +
        '</div>';

    var loadTimer = setTimeout(function () {
        container.innerHTML =
            '<div class="col-span-full text-center py-12 text-slate-400">' +
            '<p class="text-sm">Loading is taking longer than expected.</p>' +
            '<button type="button" data-cat-retry="1" class="mt-3 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-medium hover:bg-indigo-100 transition-colors">Retry</button>' +
            '</div>';
    }, 15000);

    ajaxRequest('GET', BASE_URL + 'api/categories.php?type=' + type, null, { signal: signal })
        .then(function (response) {
            clearTimeout(loadTimer);
            if (mySeq !== categoryState.seq) return;
            if (response.success && response.data) {
                renderCategories(response.data);
            } else {
                
                showToast((response && response.message) || 'Failed to load categories.', 'error');
                container.innerHTML =
                    '<div class="col-span-full text-center py-12 text-slate-400">' +
                    '<p>Failed to load categories.</p>' +
                    '<button type="button" data-cat-retry="1" class="mt-3 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-medium hover:bg-indigo-100 transition-colors">Retry</button>' +
                    '</div>';
            }
        })
        .catch(function (error) {
            clearTimeout(loadTimer);
            if (error && (error.aborted || error.stale)) return;
            if (mySeq !== categoryState.seq) return;
            var detail = (window.ExpensePro && window.ExpensePro.errors)
                ? window.ExpensePro.errors.handleApiError(error, { toast: false })
                : { message: (error && error.message) || 'Failed to load categories.' };
            showToast(detail.message || 'Failed to load categories.', 'error');
            container.innerHTML =
                '<div class="col-span-full text-center py-12 text-slate-400">' +
                '<p>Failed to load categories.</p>' +
                '<button type="button" data-cat-retry="1" class="mt-3 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-medium hover:bg-indigo-100 transition-colors">Retry</button>' +
                '</div>';
        });
}

function renderCategories(categories) {
    var container = document.getElementById('categories-list');
    if (!container) return;

    if (!categories || categories.length === 0) {
        container.innerHTML =
            '<div class="col-span-full text-center py-16">' +
            '<svg class="w-20 h-20 mx-auto mb-4 text-slate-200" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="0.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>' +
            '<p class="text-slate-400 text-sm font-medium mb-1">No ' + categoryState.activeType + ' categories yet</p>' +
            '<p class="text-slate-300 text-xs">Click "Add Category" to create one.</p>' +
            '</div>';
        return;
    }

    var html = '';
    categories.forEach(function (cat) {
        var txnCount = parseInt(cat.transaction_count || 0);
        var escapedName = escapeHtml(cat.name);
        var escapedAttrName = escapeAttr(cat.name);

        html +=
            '<div class="bg-white rounded-xl border border-slate-100 p-4 hover:border-slate-200 hover:shadow-sm transition-all card-hover">' +
            '<div class="flex items-center justify-between">' +
            '<div class="flex items-center gap-3">' +
            '<div class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-medium" style="background-color: ' + safeCssColor(cat.color) + '">' +
            getCategoryIcon(cat.icon) +
            '</div>' +
            '<div>' +
            '<p class="text-sm font-medium text-slate-900">' + escapedName + '</p>' +
            '<p class="text-xs text-slate-500">' + txnCount + ' entries this month</p>' +
            '</div>' +
            '</div>' +
            '<div class="flex items-center gap-1">' +
            '<button type="button" data-action="edit-category" data-id="' + cat.id + '" data-name="' + escapedAttrName + '" data-icon="' + escapeAttr(cat.icon) + '" data-color="' + escapeAttr(cat.color) + '" class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors" title="Edit category" aria-label="Edit ' + escapedAttrName + '">' +
            '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>' +
            '</button>' +
            '<button type="button" data-action="delete-category" data-id="' + cat.id + '" data-name="' + escapedAttrName + '" data-count="' + txnCount + '" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Delete category" aria-label="Delete ' + escapedAttrName + '">' +
            '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>' +
            '</button>' +
            '</div>' +
            '</div>' +
            '</div>';
    });

    container.innerHTML = html;

    container.querySelectorAll('[data-action="edit-category"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            openEditCategoryModal(
                parseInt(btn.dataset.id, 10) || 0,
                btn.dataset.name || '',
                btn.dataset.icon || 'tag',
                btn.dataset.color || '#6366F1'
            );
        });
    });
    container.querySelectorAll('[data-action="delete-category"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            confirmDeleteCategory(
                parseInt(btn.dataset.id, 10) || 0,
                btn.dataset.name || '',
                parseInt(btn.dataset.count, 10) || 0
            );
        });
    });
}

function initCategoryForm() {
    var form = document.getElementById('category-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitCategoryForm();
    });
}

function openCreateCategoryModal() {
    categoryState.editingId = null;
    document.getElementById('category-form').reset();
    document.getElementById('cat-form-error').classList.add('hidden');
    document.getElementById('cat-submit-btn').textContent = 'Add Category';

    var titleEl = document.querySelector('#category-modal h2');
    if (titleEl) titleEl.textContent = 'Add Category';

    document.getElementById('cat-type').value = categoryState.activeType;

    var modal = document.getElementById('category-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function openEditCategoryModal(id, name, icon, color) {
    categoryState.editingId = id;
    document.getElementById('cat-form-error').classList.add('hidden');
    document.getElementById('cat-submit-btn').textContent = 'Update Category';

    var titleEl = document.querySelector('#category-modal h2');
    if (titleEl) titleEl.textContent = 'Edit Category';

    document.getElementById('cat-name').value = name;
    document.getElementById('cat-icon').value = icon;
    document.getElementById('cat-color').value = color;

    var modal = document.getElementById('category-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeCategoryModal() {
    var modal = document.getElementById('category-modal');
    if (modal) modal.classList.add('hidden');
    categoryState.editingId = null;
}

function submitCategoryForm() {
    var name = document.getElementById('cat-name').value.trim();
    var type = document.getElementById('cat-type').value;
    var icon = document.getElementById('cat-icon').value;
    var color = document.getElementById('cat-color').value;
    var errorDiv = document.getElementById('cat-form-error');

    errorDiv.classList.add('hidden');

    if (!name || name.length < 2) {
        errorDiv.textContent = 'Category name must be at least 2 characters.';
        errorDiv.classList.remove('hidden');
        return;
    }

    showButtonLoading('cat-submit-btn');

    var isEdit = categoryState.editingId !== null;
    var method = isEdit ? 'PUT' : 'POST';
    var payload = {
        name: name,
        type: type,
        icon: icon,
        color: color
    };

    if (isEdit) {
        payload.id = categoryState.editingId;
    }

    ajaxRequest(method, BASE_URL + 'api/categories.php', payload)
    .then(function (response) {
        hideButtonLoading('cat-submit-btn');
            showToast(response.message || (isEdit ? 'Category updated!' : 'Category created!'), 'success');
            haptic(15);
        closeCategoryModal();
        loadCategories(categoryState.activeType);
    })
    .catch(function (error) {
        hideButtonLoading('cat-submit-btn');
        if (error.errors && error.errors.name) {
            errorDiv.textContent = error.errors.name;
        } else {
            errorDiv.textContent = error.message || 'Failed to save category.';
        }
        errorDiv.classList.remove('hidden');
    });
}

var deleteCatId = null;

var deleteCatName = '';

function initDeleteConfirmation() {
    var confirmBtn = document.getElementById('confirm-cat-delete-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            executeCategoryDelete();
        });
    }

    var cancelBtn = document.getElementById('cancel-cat-delete-btn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            closeCategoryDeleteModal();
        });
    }
}

function initCategoryListEvents() {
    document.addEventListener('click', function (e) {
        var el = e.target.closest('[data-cat-retry]');
        if (!el) return;
        loadCategories(categoryState.activeType);
    });
}

function confirmDeleteCategory(id, name, txnCount) {
    deleteCatId = id;
    deleteCatName = name;

    var nameEl = document.getElementById('delete-cat-name');
    if (nameEl) nameEl.textContent = name;

    var moveSection = document.getElementById('delete-cat-move-section');
    var txnCountEl = document.getElementById('delete-cat-txn-count');
    var moveSelect = document.getElementById('delete-cat-move-to');

    if (txnCount > 0) {
        moveSection.classList.remove('hidden');
        if (txnCountEl) txnCountEl.textContent = txnCount;

        if (moveSelect) {
            moveSelect.innerHTML = '<option value="">Select a category</option>';
            moveSelect.disabled = true;
            ajaxRequest('GET', BASE_URL + 'api/categories.php?type=' + categoryState.activeType)
                .then(function (response) {
                    if (response.success && response.data) {
                        response.data.forEach(function (cat) {
                            if (cat.id != id) {
                                var opt = document.createElement('option');
                                opt.value = cat.id;
                                opt.textContent = cat.name + ' (' + (parseInt(cat.transaction_count || 0)) + ' entries)';
                                moveSelect.appendChild(opt);
                            }
                        });
                    }
                    moveSelect.disabled = false;
                })
                .catch(function () {
                    moveSelect.innerHTML = '<option value="">Failed to load categories</option>';
                    moveSelect.disabled = false;
                });
        }
    } else {
        moveSection.classList.add('hidden');
        if (moveSelect) moveSelect.innerHTML = '<option value="">Select a category</option>';
    }

    var modal = document.getElementById('delete-category-modal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function executeCategoryDelete() {
    if (!deleteCatId) return;

    var moveToId = document.getElementById('delete-cat-move-to');
    var moveToVal = moveToId ? parseInt(moveToId.value) || 0 : 0;

    showButtonLoading('confirm-cat-delete-btn');

    var url = BASE_URL + 'api/categories.php?id=' + deleteCatId;
    if (moveToVal > 0) url += '&move_to_id=' + moveToVal;

    ajaxRequest('DELETE', url)
        .then(function (response) {
            hideButtonLoading('confirm-cat-delete-btn');
            showToast(response.message || 'Category deleted.', 'success');
            haptic([20, 40, 20]);
            closeCategoryDeleteModal();
            loadCategories(categoryState.activeType);
        })
        .catch(function (error) {
            hideButtonLoading('confirm-cat-delete-btn');
            showToast(error.message || 'Failed to delete category.', 'error');
        });
}

function closeCategoryDeleteModal() {
    var modal = document.getElementById('delete-category-modal');
    if (modal) modal.classList.add('hidden');
    var nameEl = document.getElementById('delete-cat-name');
    if (nameEl) nameEl.textContent = '---';
    var moveSection = document.getElementById('delete-cat-move-section');
    if (moveSection) moveSection.classList.add('hidden');
    deleteCatId = null;
}

var CATEGORY_ICONS = {
    'tag': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>',
    'briefcase': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>',
    'shopping-bag': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>',
    'truck': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>',
    'heart': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>',
    'academic-cap': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/></svg>',
    'banknotes': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15"/></svg>',
    'home': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>',
    'sparkles': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/></svg>',
    'chart-bar': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>',
    'cog': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
    'globe-alt': '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>'
};

function initIconPicker() {
    var select = document.getElementById('cat-icon');
    if (!select) return;

    select.innerHTML = '';

    Object.keys(CATEGORY_ICONS).forEach(function (key) {
        var option = document.createElement('option');
        option.value = key;
        option.textContent = key.replace(/-/g, ' ').replace(/\b\w/g, function (c) { return c.toUpperCase(); });
        select.appendChild(option);
    });
}

function getCategoryIcon(iconName) {
    return CATEGORY_ICONS[iconName] || CATEGORY_ICONS['tag'];
}
