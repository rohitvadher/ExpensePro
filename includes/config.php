<?php

header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data:; font-src 'self'; connect-src 'self' https://cdn.jsdelivr.net; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

header("X-Frame-Options: DENY");

header("X-Content-Type-Options: nosniff");

header("Referrer-Policy: same-origin");

function expenseproEnv(string $key, string $default = ''): string
{
    $value = getenv($key);
    return $value === false ? $default : (string) $value;
}

function expenseproBaseUrl(): string
{
    $configured = trim(expenseproEnv('EXPENSEPRO_BASE_URL', ''));
    if ($configured !== '') {
        return '/' . trim($configured, '/') . '/';
    }

    $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    if (preg_match('#^(.*)/api/[^/]+\.php$#', $script, $matches) === 1) {

        $directory = $matches[1] !== '' ? $matches[1] : '/';
    } else {
        $directory = $script !== '' ? dirname($script) : '/ExpensePro';
        $directory = str_replace('\\', '/', $directory);
    }
    if ($directory === '' || $directory === '.' || $directory === '/') {
        return '/';
    }
    return rtrim($directory, '/') . '/';
}

define('BASE_URL', expenseproBaseUrl());

if (session_status() === PHP_SESSION_NONE) {

    $isHttpsRequest = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

    $hostOnly = strtolower(explode(':', $_SERVER['HTTP_HOST'] ?? '', 2)[0]);
    $hostOnly = trim($hostOnly, '[]');
    $isLocalHost = in_array($hostOnly, ['localhost', '127.0.0.1', '::1'], true);
    define('COOKIE_SECURE', $isHttpsRequest && !$isLocalHost);

    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.use_only_cookies', 1);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_URL,
        'domain'   => '',
        'secure'   => COOKIE_SECURE,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

define('CSRF_REGENERATION_INTERVAL', 1800);

define('DB_HOST', expenseproEnv('EXPENSEPRO_DB_HOST', 'localhost'));
define('DB_NAME', expenseproEnv('EXPENSEPRO_DB_NAME', 'expensepro'));
define('DB_USER', expenseproEnv('EXPENSEPRO_DB_USER', 'root'));
define('DB_PASS', expenseproEnv('EXPENSEPRO_DB_PASS', ''));
define('DB_CHARSET', expenseproEnv('EXPENSEPRO_DB_CHARSET', 'utf8mb4'));
define('DB_DRIVER', 'PDO');

define('APP_NAME', 'ExpensePro');
define('STUDENT_NAME', 'Rohit Vadher');
define('APP_TAGLINE', 'Income & Expense Manager');
define('APP_VERSION', '1.0');

function appBuild(): string
{
    static $build = null;
    if ($build !== null) {
        return $build;
    }
    $tracked = [
        __DIR__ . '/../assets/css',
        __DIR__ . '/../assets/js',
        __DIR__ . '/../pages',
        __DIR__ . '/../layouts',
        __DIR__ . '/../index.php',
        __DIR__ . '/../sw.js',
        __DIR__ . '/../manifest.json',
        __DIR__ . '/../offline/offline.html',
    ];
    $max = 0;
    foreach ($tracked as $path) {
        if (is_file($path)) {
            $max = max($max, (int) filemtime($path));
        } elseif (is_dir($path)) {
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)
            );
            foreach ($it as $file) {
                if ($file->isFile()) {
                    $max = max($max, (int) $file->getMTime());
                }
            }
        }
    }
    $build = substr(md5(APP_VERSION . '|' . $max), 0, 8);
    return $build;
}

function assetUrl(string $path): string
{
    return BASE_URL . ltrim($path, '/') . '?v=' . appBuild();
}

define('SESSION_TIMEOUT', (int) expenseproEnv('EXPENSEPRO_SESSION_TIMEOUT', '86400') ?: 86400);

define('APP_DEBUG', false);

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 0);

date_default_timezone_set('Asia/Kolkata');
