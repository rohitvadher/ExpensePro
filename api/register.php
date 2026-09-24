<?php

require_once __DIR__ . '/../includes/api.php';

requireApiMethod(['POST']);
$input = requireJsonBody();
requireApiCsrf();

$name = trim((string) ($input['name'] ?? ''));
$email = trim((string) ($input['email'] ?? ''));
$password = (string) ($input['password'] ?? '');
$confirm = (string) ($input['confirm_password'] ?? '');

$errors = [];
if ($name === '') {
    $errors['name'] = 'Name is required.';
} elseif (strlen($name) < 2) {
    $errors['name'] = 'Name must be at least 2 characters.';
}

if ($email === '') {
    $errors['email'] = 'Email is required.';
} elseif (!validateEmail($email)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if ($password === '') {
    $errors['password'] = 'Password is required.';
} elseif (!validatePassword($password)) {
    $errors['password'] = 'Password must be at least 8 characters with letters and numbers.';
}

if ($password !== $confirm) {
    $errors['confirm_password'] = 'Passwords do not match.';
}

if (!empty($errors)) {
    sendError('Validation failed.', $errors, 422);
}

$regIp = apiClientIp();
$regKey = 'register:' . $regIp;
$pdo = getConnection();
if (!checkRateLimit($pdo, $regKey, 10, 3600)) {
    sendError('Too many registration attempts. Please try again later.', null, 429);
}
recordRateHit($pdo, $regKey, $regIp);

$name = sanitize($name);
$email = sanitize($email);

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    sendError('This email is already registered.', ['email' => 'Email already in use.'], 409);
}

try {
    $pdo->beginTransaction();

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $hashedPassword]);
    $userId = (int) $pdo->lastInsertId();

    $incomeCategories = [
        ['name' => 'Salary', 'icon' => 'briefcase', 'color' => '#10B981'],
        ['name' => 'Freelance', 'icon' => 'banknotes', 'color' => '#8B5CF6'],
        ['name' => 'Business', 'icon' => 'home', 'color' => '#3B82F6'],
        ['name' => 'Investment', 'icon' => 'chart-bar', 'color' => '#F59E0B'],
        ['name' => 'Rental', 'icon' => 'home', 'color' => '#EC4899'],
        ['name' => 'Other Income', 'icon' => 'sparkles', 'color' => '#6B7280'],
    ];

    $expenseCategories = [
        ['name' => 'Food & Dining', 'icon' => 'shopping-bag', 'color' => '#F59E0B'],
        ['name' => 'Transportation', 'icon' => 'truck', 'color' => '#3B82F6'],
        ['name' => 'Shopping', 'icon' => 'shopping-bag', 'color' => '#EC4899'],
        ['name' => 'Entertainment', 'icon' => 'sparkles', 'color' => '#8B5CF6'],
        ['name' => 'Bills & Utilities', 'icon' => 'cog', 'color' => '#EF4444'],
        ['name' => 'Health', 'icon' => 'heart', 'color' => '#10B981'],
        ['name' => 'Education', 'icon' => 'academic-cap', 'color' => '#6366F1'],
        ['name' => 'Rent', 'icon' => 'home', 'color' => '#F97316'],
        ['name' => 'Groceries', 'icon' => 'shopping-bag', 'color' => '#14B8A6'],
        ['name' => 'Other Expense', 'icon' => 'tag', 'color' => '#6B7280'],
    ];

    $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type, icon, color) VALUES (?, ?, ?, ?, ?)");

    foreach ($incomeCategories as $cat) {
        $stmt->execute([$userId, $cat['name'], 'income', $cat['icon'], $cat['color']]);
    }
    foreach ($expenseCategories as $cat) {
        $stmt->execute([$userId, $cat['name'], 'expense', $cat['icon'], $cat['color']]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Registration failed: ' . $e->getMessage());
    sendError('Registration failed. Please try again later.', null, 500);
}

establishSession($userId);

sendJson([
    'user' => [
        'id' => $userId,
        'name' => $name,
        'email' => $email,
    ],
    'csrf_token' => $_SESSION['csrf_token']
], 'Account created successfully! Welcome to ExpensePro.', 201);
