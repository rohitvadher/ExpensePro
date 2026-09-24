<?php


function validateEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validatePassword(string $password): bool
{
    if (strlen($password) < 8) return false;
    if (!preg_match('/[A-Za-z]/', $password)) return false;
    if (!preg_match('/[0-9]/', $password)) return false;
    return true;
}

function passwordValidationError(string $password): ?string
{
    if (strlen($password) < 8) return 'Password must be at least 8 characters.';
    if (!preg_match('/[A-Za-z]/', $password)) return 'Password must include at least one letter.';
    if (!preg_match('/[0-9]/', $password)) return 'Password must include at least one number.';
    return null;
}

function validateAmount(mixed $amount): bool
{
    return validateMoneyAmount($amount, false);
}

function validateEnum(mixed $value, array $allowed): bool
{
    return is_string($value) && in_array($value, $allowed, true);
}

function validatePositiveId(mixed $value): bool
{
    return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
}

function validateDate(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

function validateDateRange(string $from, string $to): ?string
{
    if ($from !== '' && !validateDate($from)) return 'Start date must be a valid date (YYYY-MM-DD).';
    if ($to !== '' && !validateDate($to)) return 'End date must be a valid date (YYYY-MM-DD).';
    if ($from !== '' && $to !== '' && $from > $to) return 'Start date must be before end date.';
    return null;
}

function maxMoneyAmount(): float
{
    return 9999999999.99;
}

function validateMoneyAmount(mixed $amount, bool $allowZero = false): bool
{
    if (is_bool($amount) || $amount === null) {
        return false;
    }
    if (is_string($amount)) {
        $amount = trim($amount);
        if ($amount === '' || !is_numeric($amount)) {
            return false;
        }
    } elseif (!is_int($amount) && !is_float($amount)) {
        return false;
    }

    $value = (float) $amount;
    if (!is_finite($value)) {
        return false;
    }
    if ($value < 0 || (!$allowZero && $value <= 0)) {
        return false;
    }
    if ($value > maxMoneyAmount()) {
        return false;
    }

    return abs(($value * 100) - round($value * 100)) < 0.000001;
}

function normalizeMoneyAmount(mixed $amount, bool $allowZero = false): ?float
{
    if (!validateMoneyAmount($amount, $allowZero)) {
        return null;
    }
    return moneyRound((float) $amount);
}

function moneyValidationError(mixed $amount, bool $allowZero = false): ?string
{
    if ($amount === '' || $amount === null) return 'Amount is required.';
    if (is_string($amount) && !is_numeric(trim($amount))) return 'Amount must be a valid number.';
    $value = (float) $amount;
    if (!is_finite($value) || $value < 0 || (!$allowZero && $value <= 0)) {
        return 'Amount must be greater than ₹0.00.';
    }
    if ($value > maxMoneyAmount()) return 'Amount exceeds the maximum allowed (₹9,99,99,99,999.99).';
    if (abs(($value * 100) - round($value * 100)) >= 0.000001) return 'Amount can have at most two decimal places.';
    return null;
}

function isSafeCategoryColor(mixed $color): bool
{
    return is_string($color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $color) === 1;
}

function safeCategoryColor(mixed $color): string
{
    return isSafeCategoryColor($color) ? (string) $color : '#6366F1';
}

function safeCategoryIcon(mixed $icon): string
{
    return is_string($icon) && preg_match('/^[a-zA-Z0-9_-]+$/', $icon) === 1 ? $icon : 'tag';
}

function presentCategoryFields(array $row): array
{
    if (array_key_exists('color', $row)) {
        $row['color'] = safeCategoryColor($row['color']);
    }
    if (array_key_exists('category_color', $row)) {
        $row['category_color'] = safeCategoryColor($row['category_color']);
    }
    if (array_key_exists('icon', $row)) {
        $row['icon'] = safeCategoryIcon($row['icon']);
    }
    if (array_key_exists('category_icon', $row)) {
        $row['category_icon'] = safeCategoryIcon($row['category_icon']);
    }
    return $row;
}

function validateTransactionInput(array $input): array
{
    $errors = [];
    $type = $input['type'] ?? '';
    if ($type !== 'income' && $type !== 'expense') {
        $errors['type'] = 'Select income or expense.';
    }
    if (!validatePositiveId((int) ($input['category_id'] ?? 0))) {
        $errors['category_id'] = 'Select a valid category.';
    }
    $amountError = moneyValidationError($input['amount'] ?? '');
    if ($amountError !== null) {
        $errors['amount'] = $amountError;
    }
    if (!validateDate(trim((string) ($input['date'] ?? '')))) {
        $errors['date'] = 'Enter a valid date (YYYY-MM-DD).';
    }
    $desc = trim((string) ($input['description'] ?? ''));
    if (strlen($desc) > 1000) {
        $errors['description'] = 'Description must be under 1000 characters.';
    }
    return $errors;
}

function validateCategoryInput(array $input, bool $requireType = true): array
{
    $errors = [];
    $name = sanitize((string) ($input['name'] ?? ''));
    if (strlen($name) < 2 || strlen($name) > 100) {
        $errors['name'] = 'Category name must be between 2 and 100 characters.';
    }
    if ($requireType) {
        $type = (string) ($input['type'] ?? '');
        if ($type !== 'income' && $type !== 'expense') {
            $errors['type'] = 'Type must be "income" or "expense".';
        }
    }
    if (!isSafeCategoryColor((string) ($input['color'] ?? '#6366F1'))) {
        $errors['color'] = 'Color must be a valid hex code (e.g., #6366F1).';
    }
    $rawIcon = (string) ($input['icon'] ?? 'tag');
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $rawIcon)) {
        $errors['icon'] = 'Invalid icon name.';
    }
    return $errors;
}
