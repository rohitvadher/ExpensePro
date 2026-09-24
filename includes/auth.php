<?php

function clearSessionState(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            [
                'expires' => time() - 42000,
                'path' => $params['path'] ?? BASE_URL,
                'domain' => $params['domain'] ?? '',
                'secure' => $params['secure'] ?? COOKIE_SECURE,
                'httponly' => $params['httponly'] ?? true,
                'samesite' => $params['samesite'] ?? 'Lax'
            ]
        );
    }

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
}

function establishSession(int $userId): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['last_activity'] = time();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_created'] = time();
}

function authenticateRequest(): int
{
    if (isAuthenticated()) {
        return (int) $_SESSION['user_id'];
    }
    if (checkRememberMe()) {
        return (int) ($_SESSION['user_id'] ?? 0);
    }
    return 0;
}

function isAuthenticated(): bool
{

    if (empty($_SESSION['user_id'])) {
        return false;
    }

    if (!empty($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive > SESSION_TIMEOUT) {

            clearSessionState();
            return false;
        }
    }

    $_SESSION['last_activity'] = time();
    return true;
}

function requireAuth(): void
{

    if (isAuthenticated()) {
        return;
    }

    if (checkRememberMe()) {
        return;
    }

    header('Location: ' . BASE_URL . '?page=login');
    exit;
}

function getCurrentUser(): ?array
{

    if (!isAuthenticated()) {
        return null;
    }

    if (empty($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
        clearSessionState();
        return null;
    }

    $pdo = getConnection();
    $stmt = $pdo->prepare("
        SELECT id, name, email, monthly_budget,
               DATE(created_at) AS member_since
        FROM users
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user) {
        clearSessionState();
        return null;
    }

    return $user;
}

function setRememberMe(int $userId): void
{

    $rawToken = bin2hex(random_bytes(32));

    $hashedToken = hash('sha256', $rawToken);

    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
    $stmt->execute([$hashedToken, $userId]);

    setcookie(
        'remember_token',
        $rawToken,
        [
            'expires'  => time() + 2592000, 
            'path'     => BASE_URL,
            'domain'   => '',
            'secure'   => COOKIE_SECURE,
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
}

function checkRememberMe(): bool
{

    if (empty($_COOKIE['remember_token'])) {
        return false;
    }

    $rawToken = (string) $_COOKIE['remember_token'];
    if (!ctype_xdigit($rawToken) || strlen($rawToken) !== 64) {
        clearRememberMe();
        return false;
    }
    $hashedToken = hash('sha256', $rawToken);

    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE remember_token = ?");
    $stmt->execute([$hashedToken]);
    $user = $stmt->fetch();

    if (!$user) {

        clearRememberMe();
        return false;
    }

    establishSession((int) $user['id']);

    setRememberMe((int) $user['id']);

    return true;
}

function clearRememberMe(?int $userId = null): void
{
    $ids = [];
    if ($userId !== null && $userId > 0) {
        $ids[] = $userId;
    }
    if (!empty($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
        $ids[] = (int) $_SESSION['user_id'];
    }
    if (!empty($_COOKIE['remember_token'])) {
        $presented = (string) $_COOKIE['remember_token'];
        if (ctype_xdigit($presented) && strlen($presented) === 64) {
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare('SELECT id FROM users WHERE remember_token = ? LIMIT 1');
                $stmt->execute([hash('sha256', $presented)]);
                $match = $stmt->fetch();
                if ($match) {
                    $ids[] = (int) $match['id'];
                }
            } catch (Throwable $e) {
                error_log('Remember-me lookup failed: ' . $e->getMessage());
            }
        }
    }

    $ids = array_values(array_unique($ids));
    if ($ids !== []) {
        try {
            $pdo = getConnection();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id IN ($placeholders)");
            $stmt->execute($ids);
        } catch (Throwable $e) {
            error_log('Remember-me clear failed: ' . $e->getMessage());
        }
    }

    setcookie(
        'remember_token',
        '',
        [
            'expires'  => time() - 3600, 
            'path'     => BASE_URL,
            'domain'   => '',
            'secure'   => COOKIE_SECURE,
            'httponly' => true,
            'samesite' => 'Lax'
        ]
    );
    unset($_COOKIE['remember_token']);
}

function logoutUser(): void
{
    try {
        clearRememberMe(isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);
    } catch (Throwable $e) {
        error_log('Logout remember-me cleanup failed: ' . $e->getMessage());
    }
    clearSessionState();
}
