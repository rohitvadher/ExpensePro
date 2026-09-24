<?php

require_once __DIR__ . '/../includes/api.php';

$userId = requireApiAuth();
$method = requireApiMethod(['GET', 'POST']);

$_SESSION['last_activity'] = time();

if ($method === 'GET') {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id, filename, row_count, success_count, error_count, status, created_at FROM csv_imports WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([$userId]);
    $history = $stmt->fetchAll();
    sendJson($history, 'Import history retrieved.');
}

requireApiCsrf();

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    $errorCode = isset($_FILES['csv_file']) ? $_FILES['csv_file']['error'] : 'no file';
    sendError('Please upload a valid CSV file. Error code: ' . $errorCode, null, 400);
}

$uploadedFile = $_FILES['csv_file'];
$filename = $uploadedFile['name'];
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if ($extension !== 'csv') {
    sendError('File must be a CSV (.csv) file.', null, 400);
}

if ($uploadedFile['size'] > 5 * 1024 * 1024) {
    sendError('File size must be less than 5 MB.', null, 400);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
finfo_close($finfo);
$allowedMimes = ['text/csv', 'text/plain', 'application/vnd.ms-excel', 'application/octet-stream', 'text/x-csv', 'application/csv'];
if (!in_array($mimeType, $allowedMimes, true)) {
    
    
    $peek = @file_get_contents($uploadedFile['tmp_name'], false, null, 0, 2048);
    $looksCsv = is_string($peek) && (strpos($peek, ',') !== false || strpos($peek, ';') !== false || strpos($peek, "\n") !== false);
    if (!$looksCsv) {
        sendError('Invalid file type. Only CSV files are allowed.', null, 400);
    }
}

$importKey = 'import:' . $userId;
$pdo = getConnection();
if (!checkRateLimit($pdo, $importKey, 20, 3600)) {
    sendError('Too many imports. Please try again later.', null, 429);
}
recordRateHit($pdo, $importKey, apiClientIp());

$handle = fopen($uploadedFile['tmp_name'], 'r');
if ($handle === false) {
    sendError('Failed to read the uploaded file.', null, 500);
}

$catStmt = $pdo->prepare("SELECT id, name, type FROM categories WHERE user_id = ?");
$catStmt->execute([$userId]);
$categories = $catStmt->fetchAll();

$catLookup = [];
foreach ($categories as $cat) {
    $catLookup[strtolower(trim($cat['name']))] = ['id' => (int) $cat['id'], 'type' => $cat['type']];
}

$totalRows = 0;
$imported = 0;
$skipped = 0;
$errors = [];
$importedIds = [];

$maxRows = 5000;

$normalizeDate = function (string $raw): string {
    $raw = trim($raw);
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $raw, $m)) {
        return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
    }
    if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $raw, $m)) {
        return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
    }
    if (preg_match('/^(\d{4})\/(\d{1,2})\/(\d{1,2})$/', $raw, $m)) {
        return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
    }
    return $raw;
};

$processRow = function (array $row, int $lineNumber) use ($pdo, $userId, $catLookup, $normalizeDate, &$imported, &$importedIds): ?array {
    $rowErrors = [];

    $date = $normalizeDate($row[0] ?? '');
    if (!validateDate($date)) {
        $rowErrors[] = 'Invalid date format (use YYYY-MM-DD or DD/MM/YYYY)';
    }

    $type = strtolower(trim($row[1] ?? ''));
    if (!in_array($type, ['income', 'expense'], true)) {
        if (strpos($type, 'inc') === 0) { $type = 'income'; }
        elseif (strpos($type, 'exp') === 0) { $type = 'expense'; }
        else { $rowErrors[] = 'Invalid type (use income or expense)'; }
    }

    $categoryName = strtolower(trim($row[2] ?? ''));
    $categoryId = $catLookup[$categoryName]['id'] ?? null;
    if ($categoryId === null) {
        $rowErrors[] = 'Category not found: "' . ($row[2] ?? '') . '"';
    } elseif (isset($catLookup[$categoryName]['type']) && $catLookup[$categoryName]['type'] !== $type) {
        $rowErrors[] = 'Category "' . ($row[2] ?? '') . '" is a ' . $catLookup[$categoryName]['type'] . ' category';
    }

    $amountStr = preg_replace('/[₹$,\s]/', '', trim((string) ($row[3] ?? '')));
    $amount = normalizeMoneyAmount($amountStr, false);
    if ($amount === null) {
        $rowErrors[] = 'Invalid amount (must be a positive number with up to two decimals)';
    }
    $description = trim($row[4] ?? '');
    $description = $description !== '' ? $description : null;

    if (!empty($rowErrors)) {
        return $rowErrors;
    }

    $dupCheck = $pdo->prepare("SELECT id FROM transactions WHERE user_id = ? AND category_id = ? AND type = ? AND amount = ? AND date = ? AND COALESCE(description, '') = COALESCE(?, '') LIMIT 1");
    $dupCheck->execute([$userId, $categoryId, $type, $amount, $date, $description]);
    if ($dupCheck->fetch()) {
        return ['Duplicate: same transaction already exists on ' . $date];
    }

    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, category_id, type, amount, description, date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $categoryId, $type, $amount, $description, $date]);
    $importedIds[] = (int) $pdo->lastInsertId();
    $imported++;
    return null;
};

$firstLine = fgetcsv($handle);
if ($firstLine === false) {
    fclose($handle);
    sendError('The CSV file is empty.', null, 400);
}

$knownHeaders = ['date', 'type', 'category', 'amount', 'description'];
$isHeader = false;
foreach ($firstLine as $col) {
    if (in_array(strtolower(trim($col)), $knownHeaders, true)) { $isHeader = true; break; }
}

$pdo->beginTransaction();
try {
    if (!$isHeader) {
        $totalRows++;
        $rowErrors = $processRow($firstLine, 1);
        if ($rowErrors !== null) {
            $skipped++;
            $errors[] = ['row' => 1, 'errors' => $rowErrors];
        }
    }

    $lineNumber = $isHeader ? 1 : 2;
    while (($row = fgetcsv($handle)) !== false) {
        if ($totalRows >= $maxRows) {
            $skipped++;
            $errors[] = ['row' => $lineNumber, 'errors' => ['File exceeds the 5000-row import limit']];
            break;
        }
        if (empty($row) || count($row) < 3) {
            $skipped++;
            $errors[] = ['row' => $lineNumber, 'errors' => ['Row is empty or has too few columns']];
            $lineNumber++;
            continue;
        }
        $totalRows++;
        $rowErrors = $processRow($row, $lineNumber);
        if ($rowErrors !== null) {
            $skipped++;
            $errors[] = ['row' => $lineNumber, 'errors' => $rowErrors];
        }
        $lineNumber++;
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    fclose($handle);
    error_log('CSV import failed: ' . $e->getMessage());
    sendError('Import failed partway. No rows were saved.', null, 500);
}

fclose($handle);

try {
    $errorsJson = !empty($errors) ? json_encode($errors, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) : null;
} catch (Throwable $e) {
    epLog('CSV import errors_log encoding failed: ' . $e->getMessage());
    $errorsJson = null;
}
$insertHistory = $pdo->prepare("INSERT INTO csv_imports (user_id, filename, row_count, success_count, error_count, errors_log, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
$insertHistory->execute([$userId, mb_substr($filename, 0, 255), $totalRows, $imported, $skipped, $errorsJson, $skipped === 0 && $imported > 0 ? 'completed' : ($imported > 0 ? 'partial' : 'failed')]);

sendJson(['total' => $totalRows, 'imported' => $imported, 'skipped' => $skipped, 'errors' => $errors, 'ids' => $importedIds], "Import complete. $imported transaction(s) imported, $skipped row(s) skipped.");
