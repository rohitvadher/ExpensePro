<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../backend/support/Logger.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

$isDownload = basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) === 'export.php';
if (!$isDownload) {
    header('Content-Type: application/json; charset=utf-8');
}
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');

set_exception_handler(function (Throwable $e): void {
    epLogException($e, 'API uncaught exception');
    sendJsonPayload(false, 'Something went wrong. Please try again.', null, null, 500);
});

set_error_handler(function (int $severity, string $message, string $file = '', int $line = 0): bool {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    epLog("API PHP error [$severity]: $message in $file:$line");
    return false;
});

function apiRequestMethod(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function requireApiAuth(): int
{
    $userId = authenticateRequest();
    if ($userId <= 0) {
        sendError('Please log in.', null, 401);
    }

    return $userId;
}

function requireJsonBody(): array
{
    $input = apiJsonBody();
    if (!is_array($input)) {
        sendError('Invalid JSON in request body.', null, 400);
    }
    return $input;
}

function requireApiCsrf(): void
{
    if (!verifyCsrf(requestCsrfToken())) {
        sendError('Invalid security token. Please refresh the page.', null, 403);
    }
}

function requireApiMethod(array $allowedMethods): string
{
    $method = apiRequestMethod();
    if (!in_array($method, $allowedMethods, true)) {
        sendError('Method not allowed. Use ' . implode(', ', $allowedMethods) . '.', null, 405);
    }
    return $method;
}

function requireOwnedCategory(PDO $pdo, int $userId, int $categoryId, ?string $requiredType = null): array
{
    if (!validatePositiveId($categoryId)) {
        sendError('Please select a valid category.', ['category_id' => 'A category is required.'], 422);
    }

    $stmt = $pdo->prepare('SELECT id, name, type FROM categories WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$categoryId, $userId]);
    $category = $stmt->fetch();

    if (!$category) {
        sendError('Selected category does not exist.', null, 404);
    }
    if ($requiredType !== null && $category['type'] !== $requiredType) {
        sendError('The selected category must match the transaction type.', [
            'category_id' => 'Select a ' . $requiredType . ' category.'
        ], 422);
    }

    return $category;
}
