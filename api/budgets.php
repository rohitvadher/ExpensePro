<?php

require_once __DIR__ . '/../includes/api.php';

$userId = requireApiAuth();
$method = requireApiMethod(['GET', 'POST', 'PUT', 'DELETE']);

function buildBudgetPayload(PDO $pdo, array $budget, int $userId): array
{
    $range = getBudgetPeriodRange($budget['period']);
    $params = [$userId];
    $categorySql = '';

    if ($budget['category_id'] !== null) {
        $categorySql = ' AND category_id = ?';
        $params[] = (int) $budget['category_id'];
    }
    $params[] = $range['start_date'];
    $params[] = $range['end_date'];

    $spentStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = ? AND type = 'expense'{$categorySql} AND date BETWEEN ? AND ?");
    $spentStmt->execute($params);
    $spent = (float) $spentStmt->fetchColumn();
    $status = calcBudgetStatus($spent, (float) $budget['amount']);

    return [
        'id' => (int) $budget['id'],
        'category_id' => $budget['category_id'] !== null ? (int) $budget['category_id'] : null,
        'category_name' => $budget['category_name'],
        'category_icon' => safeCategoryIcon($budget['category_icon'] ?? null),
        'category_color' => safeCategoryColor($budget['category_color'] ?? null),
        'amount' => moneyRound((float) $budget['amount']),
        'period' => $budget['period'],
        'start_date' => $range['start_date'],
        'end_date' => $range['end_date'],
        'spent' => $status['spent'],
        'remaining' => $status['remaining'],
        'percentage' => $status['percentage'],
        'is_over' => $status['is_over'],
        'created_at' => $budget['created_at']
    ];
}

function findBudget(PDO $pdo, int $budgetId, int $userId): array|false
{
    $stmt = $pdo->prepare("SELECT b.id, b.category_id, b.amount, b.period, b.start_date, b.end_date, b.created_at, c.name AS category_name, c.icon AS category_icon, c.color AS category_color FROM budgets b LEFT JOIN categories c ON c.id = b.category_id AND c.user_id = b.user_id WHERE b.id = ? AND b.user_id = ? LIMIT 1");
    $stmt->execute([$budgetId, $userId]);
    return $stmt->fetch() ?: false;
}

if ($method === 'GET') {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT b.id, b.category_id, b.amount, b.period, b.start_date, b.end_date, b.created_at, c.name AS category_name, c.icon AS category_icon, c.color AS category_color FROM budgets b LEFT JOIN categories c ON c.id = b.category_id AND c.user_id = b.user_id WHERE b.user_id = ? ORDER BY b.created_at DESC");
    $stmt->execute([$userId]);

    $budgets = array_map(fn(array $budget): array => buildBudgetPayload($pdo, $budget, $userId), $stmt->fetchAll());
    sendJson($budgets, 'Budgets retrieved successfully.');
}

if ($method === 'POST' || $method === 'PUT') {
    $input = requireJsonBody();
    requireApiCsrf();

    $id = (int) ($input['id'] ?? 0);
    $categoryId = isset($input['category_id']) && $input['category_id'] !== '' ? (int) $input['category_id'] : null;
    $amount = $input['amount'] ?? '';
    $period = $input['period'] ?? 'monthly';
    $errors = [];

    if ($method === 'PUT' && !validatePositiveId($id)) {
        $errors['id'] = 'Invalid budget ID.';
    }
    if ($categoryId !== null && !validatePositiveId($categoryId)) {
        $errors['category_id'] = 'Invalid category ID.';
    }
    if (!validateAmount($amount)) {
        $errors['amount'] = 'Amount must be a positive number.';
    }
    if (!validateEnum($period, ['weekly', 'monthly', 'yearly'])) {
        $errors['period'] = 'Period must be weekly, monthly, or yearly.';
    }
    if (!empty($errors)) {
        sendError('Please fix the errors below.', $errors, 422);
    }

    $pdo = getConnection();
    if ($method === 'PUT' && !findBudget($pdo, $id, $userId)) {
        sendError('Budget not found.', null, 404);
    }
    if ($categoryId !== null) {
        requireOwnedCategory($pdo, $userId, $categoryId, 'expense');
    }

    $duplicateStmt = $pdo->prepare("SELECT id FROM budgets WHERE user_id = ? AND period = ? AND id != ? AND ((category_id IS NULL AND ? IS NULL) OR category_id = ?) LIMIT 1");
    $duplicateStmt->execute([$userId, $period, $id, $categoryId, $categoryId]);
    if ($duplicateStmt->fetch()) {
        sendError('A budget for this category and period already exists.', null, 409);
    }

    $range = getBudgetPeriodRange($period);
    if ($method === 'POST') {
        $stmt = $pdo->prepare("INSERT INTO budgets (user_id, category_id, amount, period, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $categoryId, $amount, $period, $range['start_date'], $range['end_date']]);
        $id = (int) $pdo->lastInsertId();
        $status = 201;
        $message = 'Budget created successfully.';
    } else {
        $stmt = $pdo->prepare("UPDATE budgets SET category_id = ?, amount = ?, period = ?, start_date = ?, end_date = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$categoryId, $amount, $period, $range['start_date'], $range['end_date'], $id, $userId]);
        $status = 200;
        $message = 'Budget updated successfully.';
    }

    $budget = findBudget($pdo, $id, $userId);
    sendJson(buildBudgetPayload($pdo, $budget, $userId), $message, $status);
}

if ($method === 'DELETE') {
    $id = (int) ($_GET['id'] ?? 0);
    if (!validatePositiveId($id)) {
        sendError('Invalid budget ID.', null, 400);
    }
    requireApiCsrf();

    $pdo = getConnection();
    if (!findBudget($pdo, $id, $userId)) {
        sendError('Budget not found.', null, 404);
    }
    $stmt = $pdo->prepare('DELETE FROM budgets WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $userId]);
    sendJson(['id' => $id], 'Budget deleted successfully.');
}

sendError('Method not allowed. Use GET, POST, PUT, or DELETE.', null, 405);
