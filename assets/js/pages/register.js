document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('register-form');
    var nameInput = document.getElementById('name');
    var emailInput = document.getElementById('email');
    var passwordInput = document.getElementById('password');
    var confirmInput = document.getElementById('confirm_password');
    var errorDiv = document.getElementById('register-error');

    emailInput.addEventListener('blur', function () {
        if (this.value && !isValidEmail(this.value)) {
            showFieldError('email', 'Please enter a valid email address.');
        } else {
            clearFieldError('email');
        }
    });
    emailInput.addEventListener('input', function () { clearFieldError('email'); hideError(); });

    passwordInput.addEventListener('input', function () {
        clearFieldError('password');
        if (this.value && !isValidPassword(this.value)) {
            document.getElementById('password-hint').className = 'text-xs text-amber-500 mt-1.5';
        } else {
            document.getElementById('password-hint').className = 'text-xs text-slate-400 mt-1.5';
        }
        if (confirmInput.value) {
            if (this.value !== confirmInput.value) {
                showFieldError('confirm_password', 'Passwords do not match.');
            } else {
                clearFieldError('confirm_password');
            }
        }
        hideError();
    });

    confirmInput.addEventListener('input', function () {
        clearFieldError('confirm_password');
        if (this.value && this.value !== passwordInput.value) {
            showFieldError('confirm_password', 'Passwords do not match.');
        } else {
            clearFieldError('confirm_password');
        }
        hideError();
    });

    nameInput.addEventListener('input', function () { clearFieldError('name'); hideError(); });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideError();

        var name = nameInput.value.trim();
        var email = emailInput.value.trim();
        var password = passwordInput.value;
        var confirm = confirmInput.value;
        var isValid = true;

        if (!name) {
            showFieldError('name', 'Name is required.');
            isValid = false;
        } else if (name.length < 2) {
            showFieldError('name', 'Name must be at least 2 characters.');
            isValid = false;
        }

        if (!email) {
            showFieldError('email', 'Email is required.');
            isValid = false;
        } else if (!isValidEmail(email)) {
            showFieldError('email', 'Please enter a valid email address.');
            isValid = false;
        }

        if (!password) {
            showFieldError('password', 'Password is required.');
            isValid = false;
        } else if (!isValidPassword(password)) {
            showFieldError('password', 'Password must be at least 8 characters with letters and numbers.');
            isValid = false;
        }

        if (!confirm) {
            showFieldError('confirm_password', 'Please confirm your password.');
            isValid = false;
        } else if (password !== confirm) {
            showFieldError('confirm_password', 'Passwords do not match.');
            isValid = false;
        }

        if (!isValid) return;

        showButtonLoading('register-btn');

        ajaxRequest('POST', BASE_URL + 'api/register.php', {
            name: name,
            email: email,
            password: password,
            confirm_password: confirm,
            _csrf_token: document.querySelector('input[name="_csrf_token"]').value
        })
        .then(function (response) {
            var userName = response && response.data && response.data.user ? response.data.user.name : '';
            showToast('Welcome to ExpensePro, ' + userName + '!', 'success');
            window.location.href = BASE_URL + '?page=dashboard';
        })
        .catch(function (error) {
            var message = 'An unexpected error occurred. Please try again.';

            if (error.errors) {
                Object.keys(error.errors).forEach(function (field) {
                    showFieldError(field, error.errors[field]);
                });
                message = 'Please fix the errors below.';
            } else if (error.message) {
                message = error.message;
            }

            showError(message);
        })
        .finally(function () {
            hideButtonLoading('register-btn');
        });
    });

    function showError(message) {
        errorDiv.textContent = message;
        errorDiv.classList.remove('hidden');
    }

    function hideError() {
        errorDiv.classList.add('hidden');
    }
});
