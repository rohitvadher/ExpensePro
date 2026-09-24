document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('login-form');
    var emailInput = document.getElementById('email');
    var passwordInput = document.getElementById('password');
    var errorDiv = document.getElementById('login-error');
    var submitBtn = document.getElementById('login-btn');

    emailInput.addEventListener('blur', function () {
        if (this.value && !isValidEmail(this.value)) {
            showFieldError('email', 'Please enter a valid email address.');
        } else {
            clearFieldError('email');
        }
    });

    emailInput.addEventListener('input', function () {
        clearFieldError('email');
        hideError();
    });

    passwordInput.addEventListener('input', function () {
        clearFieldError('password');
        hideError();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideError();

        var email = emailInput.value.trim();
        var password = passwordInput.value;
        var isValid = true;

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
        }

        if (!isValid) return;

        showButtonLoading('login-btn');

        ajaxRequest('POST', BASE_URL + 'api/login.php', {
            email: email,
            password: password,
            remember_me: document.getElementById('remember_me').checked,
            _csrf_token: document.querySelector('input[name="_csrf_token"]').value
        })
        .then(function (response) {
            var userName = response && response.data && response.data.user ? response.data.user.name : '';
            showToast('Welcome back, ' + userName + '!', 'success');
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
            hideButtonLoading('login-btn');
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
