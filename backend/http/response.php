<?php


function sendJson(mixed $data, string $message = 'Success', int $statusCode = 200): void
{
    sendJsonPayload(true, $message, $data, null, $statusCode);
}

function sendError(string $message, ?array $errors = null, int $statusCode = 400): void
{
    sendJsonPayload(false, $message, null, $errors, $statusCode);
}

function sendJsonPayload(bool $success, string $message, mixed $data, ?array $errors, int $statusCode): void
{
    http_response_code($statusCode);
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    try {
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data'    => $data,
            'errors'  => $errors
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    } catch (Throwable $e) {
        epLog('JSON response encoding failed: ' . $e->getMessage());
        http_response_code(500);
        echo '{"success":false,"message":"The server returned an unreadable response.","data":null,"errors":null}';
    }
    exit;
}

function apiClientIp(): string
{
    $ip = trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    return $ip !== '' ? $ip : 'unknown';
}

function apiJsonBody(): ?array
{
    static $body = null;
    static $read = false;

    if (!$read) {
        $read = true;
        $decoded = json_decode((string) file_get_contents('php://input'), true);
        $body = is_array($decoded) ? $decoded : null;
    }

    return $body;
}

function requestCsrfToken(): string
{
    $headerToken = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($headerToken !== '') {
        return $headerToken;
    }
    $formToken = (string) ($_POST['_csrf_token'] ?? $_POST['csrf_token'] ?? '');
    if ($formToken !== '') {
        return $formToken;
    }

    $body = apiJsonBody();
    if (is_array($body)) {
        return (string) ($body['_csrf_token'] ?? $body['csrf_token'] ?? '');
    }
    return '';
}
