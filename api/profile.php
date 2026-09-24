<?php

require_once __DIR__ . '/../includes/api.php';

$userId = requireApiAuth();
$method = requireApiMethod(['GET', 'PUT']);

function changeUserPassword(PDO $pdo, int $userId, string $currentPassword, string $newPassword): void
{
    if ($currentPassword === '') {
        sendError('Current password is required.', ['current_password' => 'Current password is required.'], 401);
    }
    if (!validatePassword($newPassword)) {
        sendError('New password must be at least 8 characters with letters and numbers.', ['new_password' => 'Password too weak.'], 422);
    }

    $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        sendError('Current password is incorrect.', ['current_password' => 'Incorrect password.'], 401);
    }

    $hashed = password_hash($newPassword, PASSWORD_BCRYPT);
    $update = $pdo->prepare('UPDATE users SET password = ?, remember_token = NULL WHERE id = ?');
    $update->execute([$hashed, $userId]);
    establishSession($userId);
}

if ($method === 'GET') {
    $pdo = getConnection();
    $user = getCurrentUser();
    if ($user === null) {
        sendError('User not found.', null, 404);
    }

    $monthStart = date('Y-m-01');
    $monthEnd = date('Y-m-t');
    $statsStmt = $pdo->prepare("SELECT COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS total_income, COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS total_expense, COUNT(*) AS transaction_count FROM transactions WHERE user_id = ? AND date >= ? AND date <= ?");
    $statsStmt->execute([$userId, $monthStart, $monthEnd]);
    $stats = $statsStmt->fetch();
    $totals = calcTotals((float) $stats['total_income'], (float) $stats['total_expense']);

    $response = [
        'user' => [
            'id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email'],
            'monthly_budget' => $user['monthly_budget'] ? (float) $user['monthly_budget'] : null,
            'member_since' => $user['member_since'],
        ],
        'stats' => [
            'total_income' => $totals['income'],
            'total_expense' => $totals['expense'],
            'balance' => $totals['balance'],
            'transaction_count' => (int) $stats['transaction_count']
        ]
    ];
    sendJson($response, 'Profile data retrieved successfully.');
}

if ($method === 'PUT') {
    $input = requireJsonBody();
    requireApiCsrf();

    $action = $input['action'] ?? 'update_profile';

    if ($action === 'change_password') {
        $currentPassword = (string) ($input['current_password'] ?? '');
        $newPassword = (string) ($input['new_password'] ?? '');
        $confirmPassword = (string) ($input['confirm_password'] ?? '');

        if ($newPassword !== $confirmPassword) {
            sendError('Passwords do not match.', ['confirm_password' => 'Passwords do not match.'], 422);
        }

        $pdo = getConnection();
        changeUserPassword($pdo, $userId, $currentPassword, $newPassword);
        sendJson(['csrf_token' => $_SESSION['csrf_token'] ?? ''], 'Password changed successfully.');
    }

    $name = trim((string) ($input['name'] ?? ''));
    $email = trim((string) ($input['email'] ?? ''));
    $monthlyBudget = $input['monthly_budget'] ?? null;
    $currentPassword = (string) ($input['current_password'] ?? '');
    $newPassword = (string) ($input['new_password'] ?? '');
    $confirmPassword = (string) ($input['confirm_password'] ?? '');

    $errors = [];
    if (strlen($name) < 2 || strlen($name) > 100) $errors['name'] = 'Name must be between 2 and 100 characters.';
    if ($email === '' || !validateEmail($email)) $errors['email'] = 'Please enter a valid email address.';
    if ($monthlyBudget !== null && $monthlyBudget !== '') {
        if (!validateAmount($monthlyBudget)) $errors['monthly_budget'] = 'Monthly budget must be a positive number.';
    }
    if ($currentPassword === '') $errors['current_password'] = 'Current password is required to save changes.';
    if ($newPassword !== '' && !validatePassword($newPassword)) $errors['new_password'] = 'Password must be at least 8 characters with letters and numbers.';
    if ($newPassword !== '' && $newPassword !== $confirmPassword) $errors['confirm_password'] = 'New passwords do not match.';
    if (!empty($errors)) sendError('Please fix the errors below.', $errors, 422);

    $pdo = getConnection();
    $passwordStmt = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1');
    $passwordStmt->execute([$userId]);
    $passwordRow = $passwordStmt->fetch();
    if (!$passwordRow || !password_verify($currentPassword, $passwordRow['password'])) {
        sendError('Current password is incorrect.', ['current_password' => 'Incorrect current password.'], 401);
    }

    $dupStmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1');
    $dupStmt->execute([$email, $userId]);
    if ($dupStmt->fetch()) {
        sendError('This email is already in use by another account.', ['email' => 'Email already in use.'], 409);
    }

    $budgetValue = ($monthlyBudget !== null && $monthlyBudget !== '') ? (float) $monthlyBudget : null;
    $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, monthly_budget = ? WHERE id = ?');
    $stmt->execute([$name, $email, $budgetValue, $userId]);

    if ($newPassword !== '') {
        changeUserPassword($pdo, $userId, $currentPassword, $newPassword);
    }

    $user = getCurrentUser();
    if ($user === null) {
        sendError('User not found after update.', null, 404);
    }

    sendJson([
        'user' => [
            'id' => (int) $user['id'], 'name' => $user['name'], 'email' => $user['email'],
            'monthly_budget' => $user['monthly_budget'] ? (float) $user['monthly_budget'] : null,
            'member_since' => $user['member_since']
        ],
        'csrf_token' => $_SESSION['csrf_token'] ?? ''
    ], 'Profile updated successfully.');
}

sendError('Method not allowed. Use GET or PUT.', null, 405);
