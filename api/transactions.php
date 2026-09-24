<?php

require_once __DIR__ . '/../includes/api.php';

$userId = requireApiAuth();
$method = requireApiMethod(['GET', 'POST', 'PUT', 'DELETE']);

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'single') {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            sendError('Invalid transaction ID.', null, 400);
        }

        $pdo = getConnection();
        $stmt = $pdo->prepare("
            SELECT t.id, t.user_id, t.category_id, t.type, t.amount, t.description,
                   t.date, t.created_at, t.updated_at,
                   c.name AS category_name, c.icon AS category_icon, c.color AS category_color
            FROM transactions t
            JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id
            WHERE t.id = ? AND t.user_id = ?
            LIMIT 1
        ");
        $stmt->execute([$id, $userId]);
        $transaction = $stmt->fetch();

        if (!$transaction) {
            sendError('Transaction not found.', null, 404);
        }

        sendJson(presentCategoryFields($transaction), 'Transaction retrieved successfully.');
    }

    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, max(1, intval($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    $type = $_GET['type'] ?? '';
    $search = trim($_GET['search'] ?? '');
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    $categoryId = intval($_GET['category_id'] ?? 0);

    $pdo = getConnection();
    $where = ['t.user_id = ?'];
    $params = [$userId];

    if ($type === 'income' || $type === 'expense') {
        $where[] = 't.type = ?';
        $params[] = $type;
    }

    if ($search !== '') {
        $escapedSearch = addcslashes($search, '%_');
        $where[] = 't.description LIKE ?';
        $params[] = '%' . $escapedSearch . '%';
    }

    if (validateDate($dateFrom)) {
        $where[] = 't.date >= ?';
        $params[] = $dateFrom;
    }
    if (validateDate($dateTo)) {
        $where[] = 't.date <= ?';
        $params[] = $dateTo;
    }
    if ($categoryId > 0) {
        $where[] = 't.category_id = ?';
        $params[] = $categoryId;
    }

    $whereClause = implode(' AND ', $where);

    if (validateDate($dateFrom) && validateDate($dateTo) && $dateFrom > $dateTo) {
        sendError('Invalid date range.', ['date_from' => 'Start date must be before end date.'], 422);
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) AS total FROM transactions t WHERE $whereClause");
    $countStmt->execute($params);
    $countRow = $countStmt->fetch();
    $total = $countRow ? (int) $countRow['total'] : 0;

    $dataStmt = $pdo->prepare("
        SELECT t.id, t.user_id, t.category_id, t.type, t.amount, t.description,
               t.date, t.created_at, t.updated_at,
               c.name AS category_name, c.icon AS category_icon, c.color AS category_color
        FROM transactions t
        JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id
        WHERE $whereClause
        ORDER BY t.date DESC, t.id DESC
        LIMIT $limit OFFSET $offset
    ");
    $dataStmt->execute($params);
    $transactions = $dataStmt->fetchAll();

    sendJson([
        'transactions' => array_map('presentCategoryFields', $transactions),
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int) $total,
            'total_pages' => max(1, ceil($total / $limit))
        ]
    ], 'Transactions retrieved successfully.');
}

if ($method === 'POST') {
    $input = requireJsonBody();
    requireApiCsrf();

    $type = $input['type'] ?? '';
    $categoryId = intval($input['category_id'] ?? 0);
    $amount = $input['amount'] ?? '';
    $description = trim($input['description'] ?? '');
    $date = trim($input['date'] ?? '');

    $errors = [];
    if ($type !== 'income' && $type !== 'expense') {
        $errors['type'] = 'Type must be "income" or "expense".';
    }
    if ($categoryId <= 0) {
        $errors['category_id'] = 'Please select a category.';
    }
    if (!validateAmount($amount)) {
        $errors['amount'] = 'Amount must be a positive number.';
    }
    if (!validateDate($date)) {
        $errors['date'] = 'Please enter a valid date (YYYY-MM-DD).';
    }
    if (!empty($errors)) {
        sendError('Please fix the errors below.', $errors, 422);
    }

    $amount = (float) $amount;

    $description = $description !== '' ? $description : null;
    $pdo = getConnection();
    requireOwnedCategory($pdo, $userId, $categoryId, $type);

    $dupStmt = $pdo->prepare("
        SELECT id, created_at FROM transactions
        WHERE user_id = ? AND category_id = ? AND type = ?
              AND amount = ? AND date = ?
              AND COALESCE(description, '') = COALESCE(?, '')
        LIMIT 1
    ");
    $dupStmt->execute([$userId, $categoryId, $type, $amount, $date, $description]);
    $existing = $dupStmt->fetch();
    if ($existing) {
        sendError('A duplicate transaction already exists. Please edit the existing one instead.',
            ['duplicate_id' => (int) $existing['id'], 'duplicate_date' => $existing['created_at']], 409);
    }

    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, category_id, type, amount, description, date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $categoryId, $type, $amount, $description, $date]);
    $newId = $pdo->lastInsertId();

    $fetchStmt = $pdo->prepare("
        SELECT t.id, t.user_id, t.category_id, t.type, t.amount, t.description,
               t.date, t.created_at, t.updated_at,
               c.name AS category_name, c.icon AS category_icon, c.color AS category_color
        FROM transactions t JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id WHERE t.id = ? AND t.user_id = ?
    ");
    $fetchStmt->execute([$newId, $userId]);
    $transaction = $fetchStmt->fetch();

    sendJson(presentCategoryFields($transaction), 'Transaction created successfully.', 201);
}

if ($method === 'PUT') {
    $input = requireJsonBody();
    requireApiCsrf();

    $id = intval($input['id'] ?? 0);
    $type = $input['type'] ?? '';
    $categoryId = intval($input['category_id'] ?? 0);
    $amount = $input['amount'] ?? '';
    $description = trim($input['description'] ?? '');
    $date = trim($input['date'] ?? '');

    if ($id <= 0) {
        sendError('Invalid transaction ID.', null, 400);
    }

    $errors = [];
    if ($type !== 'income' && $type !== 'expense') {
        $errors['type'] = 'Type must be "income" or "expense".';
    }
    if ($categoryId <= 0) {
        $errors['category_id'] = 'Please select a category.';
    }
    if (!validateAmount($amount)) {
        $errors['amount'] = 'Amount must be a positive number.';
    }
    if (!validateDate($date)) {
        $errors['date'] = 'Please enter a valid date (YYYY-MM-DD).';
    }
    if (!empty($errors)) {
        sendError('Please fix the errors below.', $errors, 422);
    }

    $amount = (float) $amount;
    $pdo = getConnection();

    $checkStmt = $pdo->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ? LIMIT 1");
    $checkStmt->execute([$id, $userId]);
    if (!$checkStmt->fetch()) {
        sendError('Transaction not found.', null, 404);
    }

    requireOwnedCategory($pdo, $userId, $categoryId, $type);

    $stmt = $pdo->prepare("UPDATE transactions SET type = ?, category_id = ?, amount = ?, description = ?, date = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$type, $categoryId, $amount, $description, $date, $id, $userId]);

    $fetchStmt = $pdo->prepare("
        SELECT t.id, t.user_id, t.category_id, t.type, t.amount, t.description,
               t.date, t.created_at, t.updated_at,
               c.name AS category_name, c.icon AS category_icon, c.color AS category_color
        FROM transactions t JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id WHERE t.id = ? AND t.user_id = ?
    ");
    $fetchStmt->execute([$id, $userId]);
    $transaction = $fetchStmt->fetch();

    sendJson(presentCategoryFields($transaction), 'Transaction updated successfully.');
}

if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) {
        sendError('Invalid transaction ID.', null, 400);
    }
    requireApiCsrf();

    $pdo = getConnection();
    $checkStmt = $pdo->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ? LIMIT 1");
    $checkStmt->execute([$id, $userId]);
    if (!$checkStmt->fetch()) {
        sendError('Transaction not found.', null, 404);
    }

    $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    sendJson(['id' => $id], 'Transaction deleted successfully.');
}

sendError('Method not allowed. Use GET, POST, PUT, or DELETE.', null, 405);
