<?php

require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

echo json_encode([
    'success' => true,
    'message' => 'Version retrieved.',
    'data' => ['version' => APP_VERSION, 'build' => appBuild()],
    'errors' => null,
]);
