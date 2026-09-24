<?php

require_once __DIR__ . '/../includes/api.php';

requireApiMethod(['POST']);
$input = requireJsonBody();
requireApiCsrf();

$clientIp = apiClientIp();

$pdo = getConnection();

try {
    $pdo->exec("DELETE FROM login_attempts WHERE attempted_at < (NOW() - INTERVAL 15 MINUTE)");
} catch (Throwable $e) { error_log('Login attempt cleanup failed: ' . $e->getMessage()); }

$email = trim($input['email'] ?? '');
$password = (string) ($input['password'] ?? '');

$errors = [];
if (empty($email)) {
    $errors['email'] = 'Email is required.';
} elseif (!validateEmail($email)) {
    $errors['email'] = 'Please enter a valid email address.';
}
if (empty($password)) {
    $errors['password'] = 'Password is required.';
}

if (!empty($errors)) {
    sendError('Validation failed.', $errors, 422);
}

$rateStmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND ip = ? AND attempted_at >= (NOW() - INTERVAL 15 MINUTE)");
$rateStmt->execute([$email, $clientIp]);
if ((int) $rateStmt->fetchColumn() >= 5) {
    sendError('Too many failed attempts. Please try again in 15 minutes.', null, 429);
}

$recordAttempt = function () use ($pdo, $email, $clientIp): void {
    try {
        $ins = $pdo->prepare("INSERT INTO login_attempts (email, ip) VALUES (?, ?)");
        $ins->execute([$email, $clientIp]);
    } catch (Throwable $e) { error_log('Login attempt log failed: ' . $e->getMessage()); }
};

$stmt = $pdo->prepare("SELECT id, name, email, password, monthly_budget FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    password_verify($password, '$2y$10$' . str_repeat('0', 53) . '0');
    $recordAttempt();
    sendError('Invalid email or password.', null, 401);
}

if (!password_verify($password, $user['password'])) {
    $recordAttempt();
    sendError('Invalid email or password.', null, 401);
}

if (password_needs_rehash($user['password'], PASSWORD_BCRYPT)) {
    try {
        $rehashStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $rehashStmt->execute([password_hash($password, PASSWORD_BCRYPT), (int) $user['id']]);
    } catch (Throwable $e) { error_log('Password rehash failed: ' . $e->getMessage()); }
}

try {
    $clearStmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ? AND ip = ?");
    $clearStmt->execute([$email, $clientIp]);
} catch (Throwable $e) { error_log('Login attempt clear failed: ' . $e->getMessage()); }

$rememberMe = !empty($input['remember_me']);
if ($rememberMe) {
    setRememberMe((int) $user['id']);
} else {
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
    $stmt->execute([(int) $user['id']]);
}

establishSession((int) $user['id']);

recordLoginNotification($pdo, (int) $user['id']);

$response = [
    'user' => [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'monthly_budget' => $user['monthly_budget'] ? (float) $user['monthly_budget'] : null,
    ],
    'csrf_token' => $_SESSION['csrf_token']
];

sendJson($response, 'Login successful.', 200);
