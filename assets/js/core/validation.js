(function () {
    'use strict';

    var MAX_MONEY = 9999999999.99;

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email || ''));
    }

    function isValidPassword(password) {
        password = String(password || '');
        if (password.length < 8) return false;
        if (!/[A-Za-z]/.test(password)) return false;
        if (!/[0-9]/.test(password)) return false;
        return true;
    }

    function passwordError(password) {
        password = String(password || '');
        if (!password) return 'Password is required.';
        if (password.length < 8) return 'Password must be at least 8 characters.';
        if (!/[A-Za-z]/.test(password)) return 'Password must include at least one letter.';
        if (!/[0-9]/.test(password)) return 'Password must include at least one number.';
        return null;
    }

    function moneyError(amount) {
        if (amount === '' || amount === null || amount === undefined) return 'Amount is required.';
        var str = String(amount).trim();
        if (str === '' || isNaN(Number(str))) return 'Enter a valid amount.';
        var value = Number(str);
        if (!isFinite(value) || value <= 0) return 'Amount must be greater than ₹0.00.';
        if (value > MAX_MONEY) return 'Amount is too large.';
        if (Math.abs((value * 100) - Math.round(value * 100)) > 0.000001) return 'Amount can have at most two decimals.';
        return null;
    }

    function dateError(dateStr) {
        var s = String(dateStr || '').trim();
        if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) return 'Enter a valid date (YYYY-MM-DD).';
        var parts = s.split('-').map(Number);
        var d = new Date(parts[0], parts[1] - 1, parts[2]);
        if (d.getFullYear() !== parts[0] || d.getMonth() !== parts[1] - 1 || d.getDate() !== parts[2]) {
            return 'Enter a valid date (YYYY-MM-DD).';
        }
        return null;
    }

    function dateRangeError(from, to) {
        var fromErr = from ? dateError(from) : null;
        if (fromErr) return { field: 'date_from', message: fromErr };
        var toErr = to ? dateError(to) : null;
        if (toErr) return { field: 'date_to', message: toErr };
        if (from && to && from > to) return { field: 'date_from', message: 'Start date must be before end date.' };
        return null;
    }

    function validateTransaction(data) {
        var errors = {};
        if (data.type !== 'income' && data.type !== 'expense') {
            errors.type = 'Select income or expense.';
        }
        if (!data.category_id || Number(data.category_id) <= 0) {
            errors.category_id = data.type
                ? 'Select an ' + data.type + ' category before saving this transaction.'
                : 'Select a valid category.';
        }
        var amountErr = moneyError(data.amount);
        if (amountErr) errors.amount = amountErr;
        var dErr = dateError(data.date);
        if (dErr) errors.date = dErr;
        if (data.description && String(data.description).length > 1000) {
            errors.description = 'Description must be under 1000 characters.';
        }
        return errors;
    }

    function validateCategory(data, requireType) {
        var errors = {};
        var name = String(data.name || '').trim();
        if (name.length < 2 || name.length > 100) {
            errors.name = 'Category name must be between 2 and 100 characters.';
        }
        if (requireType !== false && data.type !== 'income' && data.type !== 'expense') {
            errors.type = 'Type must be "income" or "expense".';
        }
        if (data.color && !/^#[0-9A-Fa-f]{6}$/.test(String(data.color))) {
            errors.color = 'Color must be a valid hex code (e.g., #6366F1).';
        }
        if (data.icon && !/^[a-zA-Z0-9_-]+$/.test(String(data.icon))) {
            errors.icon = 'Invalid icon name.';
        }
        return errors;
    }

    window.ExpensePro = window.ExpensePro || {};
    window.ExpensePro.validation = {
        isValidEmail: isValidEmail,
        isValidPassword: isValidPassword,
        passwordError: passwordError,
        moneyError: moneyError,
        dateError: dateError,
        dateRangeError: dateRangeError,
        validateTransaction: validateTransaction,
        validateCategory: validateCategory,
        MAX_MONEY: MAX_MONEY
    };
})();
