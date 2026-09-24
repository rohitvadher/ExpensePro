var profileAbort = null;
var profileSeq = 0;

document.addEventListener('DOMContentLoaded', function () {
    loadProfileStats();
    initProfileEditForm();
});

function loadProfileStats() {
    Loader.inline('profile-stats', 'Loading stats\u2026');
    if (profileAbort) {
        try { profileAbort.abort(); } catch (e) {}
    }
    profileAbort = ('AbortController' in window) ? new AbortController() : null;
    var signal = profileAbort ? profileAbort.signal : undefined;
    var mySeq = ++profileSeq;
    ajaxRequest('GET', BASE_URL + 'api/profile.php', null, { signal: signal })
        .then(function (response) {
            if (mySeq !== profileSeq) return;
            if (response.success && response.data) {
                renderProfileStats(response.data.stats);
            } else {
                
                showToast((response && response.message) || 'Failed to load profile data.', 'error');
                var statsEl2 = document.getElementById('profile-stats');
                if (statsEl2) {
                    statsEl2.innerHTML = '<div class="col-span-full text-center py-8 text-rose-400 text-sm">Failed to load stats</div>';
                }
            }
        })
        .catch(function (error) {
            if (error && (error.aborted || error.stale)) return;
            if (mySeq !== profileSeq) return;
            var detail = (window.ExpensePro && window.ExpensePro.errors)
                ? window.ExpensePro.errors.handleApiError(error, { toast: false })
                : { message: (error && error.message) || 'Failed to load profile.' };
            showToast(detail.message || 'Failed to load profile.', 'error');
            var statsEl = document.getElementById('profile-stats');
            if (statsEl) {
                statsEl.innerHTML = '<div class="col-span-full text-center py-8 text-rose-400 text-sm">Failed to load stats</div>';
            }
        });
}

function renderProfileStats(stats) {
    var container = document.getElementById('profile-stats');
    if (!container) return;

    var cards = [
        {
            label: 'Total Income',
            value: '\u20B9' + (stats.total_income || 0).toLocaleString('en-IN', { minimumFractionDigits: 0 }),
            color: 'emerald',
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m0 0l6.75-6.75M12 19.5l-6.75-6.75"/>'
        },
        {
            label: 'Total Expense',
            value: '\u20B9' + (stats.total_expense || 0).toLocaleString('en-IN', { minimumFractionDigits: 0 }),
            color: 'rose',
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5v-15m0 0l-6.75 6.75M12 4.5l6.75 6.75"/>'
        },
        {
            label: 'Balance',
            value: (stats.balance < 0 ? '-\u20B9' : '\u20B9') + Math.abs(stats.balance || 0).toLocaleString('en-IN', { minimumFractionDigits: 0 }),
            color: stats.balance >= 0 ? 'indigo' : 'rose',
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15"/>'
        },
        {
            label: 'Transactions',
            value: (stats.transaction_count || 0).toLocaleString('en-IN'),
            color: 'amber',
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>'
        }
    ];

    var colorMap = { emerald: '#10B981', rose: '#F43F5E', indigo: '#6366F1', amber: '#F59E0B' };
    var html = '';
    cards.forEach(function (card) {
        var color = colorMap[card.color] || '#6366F1';

        html +=
            '<div class="bg-white rounded-xl border border-slate-100 p-4 card-hover">' +
            '<div class="flex items-center justify-between mb-2">' +
            '<span class="text-xs font-medium text-slate-500 uppercase tracking-wider">' + escapeHtml(card.label) + '</span>' +
            '<div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: ' + color + '15; color: ' + color + '">' +
            '<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">' +
            card.icon +
            '</svg>' +
            '</div>' +
            '</div>' +
            '<p class="text-xl font-bold text-slate-900">' + escapeHtml(card.value) + '</p>' +
            '</div>';
    });

    container.innerHTML = html;
}

function toggleProfileEdit() {
    var section = document.getElementById('profile-edit-section');
    if (!section) return;

    var isHidden = section.classList.contains('hidden');
    if (isHidden) {
        section.classList.remove('hidden');
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        section.classList.add('hidden');
        var errorDiv = document.getElementById('profile-form-error');
        var successDiv = document.getElementById('profile-form-success');
        if (errorDiv) errorDiv.classList.add('hidden');
        if (successDiv) successDiv.classList.add('hidden');
    }
}

function initProfileEditForm() {
    var form = document.getElementById('profile-edit-form');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        submitProfileEdit();
    });
}

function submitProfileEdit() {
    var name = document.getElementById('edit-name').value.trim();
    var email = document.getElementById('edit-email').value.trim();
    var monthlyBudget = document.getElementById('edit-monthly-budget').value;
    var currentPassword = document.getElementById('edit-current-password').value;
    var newPassword = document.getElementById('edit-new-password').value;
    var confirmPassword = document.getElementById('edit-confirm-password').value;
    var errorDiv = document.getElementById('profile-form-error');
    var successDiv = document.getElementById('profile-form-success');

    errorDiv.classList.add('hidden');
    successDiv.classList.add('hidden');

    var errors = [];

    if (name.length < 2 || name.length > 100) {
        errors.push('Name must be between 2 and 100 characters.');
        showFieldError('edit-name', 'Name must be between 2 and 100 characters.');
    } else {
        clearFieldError('edit-name');
    }

    if (!email || !isValidEmail(email)) {
        errors.push('Please enter a valid email address.');
        showFieldError('edit-email', 'Please enter a valid email address.');
    } else {
        clearFieldError('edit-email');
    }

    if (!currentPassword) {
        errors.push('Current password is required to save changes.');
        showFieldError('edit-current-password', 'Current password is required.');
    } else {
        clearFieldError('edit-current-password');
    }

    if (newPassword && !isValidPassword(newPassword)) {
        errors.push('New password must be at least 8 characters with 1 letter and 1 number.');
        showFieldError('edit-new-password', 'Password must be at least 8 characters with 1 letter and 1 number.');
    } else {
        clearFieldError('edit-new-password');
    }

    if (newPassword && newPassword !== confirmPassword) {
        errors.push('New passwords do not match.');
        showFieldError('edit-confirm-password', 'Passwords do not match.');
    } else {
        clearFieldError('edit-confirm-password');
    }

    if (errors.length > 0) {
        errorDiv.textContent = errors.join(' ');
        errorDiv.classList.remove('hidden');
        return;
    }

    var payload = {
        name: name,
        email: email,
        monthly_budget: monthlyBudget !== '' ? parseFloat(monthlyBudget) : null,
        current_password: currentPassword,
        _csrf_token: document.querySelector('input[name="_csrf_token"]').value
    };

    if (newPassword) {
        payload.new_password = newPassword;
        payload.confirm_password = confirmPassword;
    }

    showButtonLoading('profile-save-btn');

    ajaxRequest('PUT', BASE_URL + 'api/profile.php', payload)
        .then(function (response) {
            hideButtonLoading('profile-save-btn');
            
            if (response && response.data && response.data.csrf_token) {
                refreshCsrfToken(response.data.csrf_token);
            }
            showToast(response.message || 'Profile updated successfully!', 'success');

            successDiv.textContent = 'Profile updated successfully!';
            successDiv.classList.remove('hidden');
            errorDiv.classList.add('hidden');

            document.getElementById('edit-current-password').value = '';
            document.getElementById('edit-new-password').value = '';
            document.getElementById('edit-confirm-password').value = '';

            if (response.data && response.data.user) {
                var u = response.data.user;
                var nameEl = document.getElementById('profile-name-display');
                var emailEl = document.getElementById('profile-email-display');
                if (nameEl) nameEl.textContent = u.name || '';
                if (emailEl) emailEl.textContent = u.email || '';
            }

            toggleProfileEdit();
        })
        .catch(function (error) {
            hideButtonLoading('profile-save-btn');

            if (error.errors) {
                var errorMessages = [];
                Object.keys(error.errors).forEach(function (key) {
                    errorMessages.push(String(error.errors[key]));
                });
                errorDiv.textContent = errorMessages.join(' ');
            } else {
                errorDiv.textContent = error.message || 'Failed to update profile.';
            }
            errorDiv.classList.remove('hidden');
            successDiv.classList.add('hidden');
        });
}
