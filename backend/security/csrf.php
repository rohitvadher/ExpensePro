<?php


function generateToken(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
        $_SESSION['csrf_token_created'] = time();
    } elseif (isset($_SESSION['csrf_token_created'])
        && defined('CSRF_REGENERATION_INTERVAL')
        && (time() - (int) $_SESSION['csrf_token_created']) > CSRF_REGENERATION_INTERVAL) {
        $_SESSION['csrf_token'] = generateToken();
        $_SESSION['csrf_token_created'] = time();
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(string $token): bool
{
    return !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}
