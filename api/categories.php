<?php

require_once __DIR__ . '/../includes/api.php';

$userId = requireApiAuth();
requireApiMethod(['GET', 'POST', 'PUT', 'DELETE']);
$method = apiRequestMethod();

$_SESSION['last_activity'] = time();

if ($method === 'GET') {
    $type = $_GET['type'] ?? '';
    $pdo = getConnection();

    if ($type === 'income' || $type === 'expense') {
        $firstOfMonth = date('Y-m-01');
        $stmt = $pdo->prepare("
            SELECT c.id, c.user_id, c.name, c.type, c.icon, c.color, c.created_at,
                   COUNT(t.id) AS transaction_count
            FROM categories c
            LEFT JOIN transactions t ON t.category_id = c.id AND t.user_id = c.user_id AND t.date >= ?
            WHERE c.user_id = ? AND c.type = ?
            GROUP BY c.id, c.user_id, c.name, c.type, c.icon, c.color, c.created_at
            ORDER BY c.name ASC
        ");
        $stmt->execute([$firstOfMonth, $userId, $type]);
    } else {
        $firstOfMonth = date('Y-m-01');
        $stmt = $pdo->prepare("
            SELECT c.id, c.user_id, c.name, c.type, c.icon, c.color, c.created_at,
                   COUNT(t.id) AS transaction_count
            FROM categories c
            LEFT JOIN transactions t ON t.category_id = c.id AND t.user_id = c.user_id AND t.date >= ?
            WHERE c.user_id = ?
            GROUP BY c.id, c.user_id, c.name, c.type, c.icon, c.color, c.created_at
            ORDER BY c.type DESC, c.name ASC
        ");
        $stmt->execute([$firstOfMonth, $userId]);
    }
    $categories = $stmt->fetchAll();
    $categories = array_map(function (array $category): array {
        $category['color'] = safeCategoryColor($category['color'] ?? null);
        $category['icon'] = safeCategoryIcon($category['icon'] ?? null);
        $category['transaction_count'] = (int) ($category['transaction_count'] ?? 0);
        return $category;
    }, $categories);
    sendJson($categories, 'Categories retrieved successfully.');
}

if ($method === 'POST') {
    $input = requireJsonBody();
    requireApiCsrf();

    $name = sanitize((string) ($input['name'] ?? ''));
    $type = (string) ($input['type'] ?? '');
    $icon = safeCategoryIcon($input['icon'] ?? 'tag');
    $color = (string) ($input['color'] ?? '#6366F1');

    $errors = [];
    if (strlen($name) < 2 || strlen($name) > 100) {
        $errors['name'] = 'Category name must be between 2 and 100 characters.';
    }
    if ($type !== 'income' && $type !== 'expense') {
        $errors['type'] = 'Type must be "income" or "expense".';
    }
    if (!isSafeCategoryColor($color)) {
        $errors['color'] = 'Color must be a valid hex code (e.g., #6366F1).';
    }
    if ($icon === 'tag' && !preg_match('/^[a-zA-Z0-9_-]+$/', (string) ($input['icon'] ?? 'tag'))) {
        $errors['icon'] = 'Invalid icon name.';
    }
    if (!empty($errors)) {
        sendError('Please fix the errors below.', $errors, 422);
    }

    $pdo = getConnection();
    $dupStmt = $pdo->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ? AND type = ? LIMIT 1");
    $dupStmt->execute([$userId, $name, $type]);
    if ($dupStmt->fetch()) {
        sendError('A category with this name already exists.', null, 409);
    }

    $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type, icon, color) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $name, $type, $icon, $color]);
    $newId = $pdo->lastInsertId();

    $fetchStmt = $pdo->prepare("SELECT id, user_id, name, type, icon, color, created_at FROM categories WHERE id = ? AND user_id = ?");
    $fetchStmt->execute([$newId, $userId]);
    $category = $fetchStmt->fetch();

    sendJson($category, 'Category created successfully.', 201);
}

if ($method === 'PUT') {
    $input = requireJsonBody();
    requireApiCsrf();

    $id = intval($input['id'] ?? 0);
    $name = sanitize((string) ($input['name'] ?? ''));
    $icon = safeCategoryIcon($input['icon'] ?? 'tag');
    $color = (string) ($input['color'] ?? '#6366F1');

    if ($id <= 0) {
        sendError('Invalid category ID.', null, 400);
    }

    $errors = [];
    if (strlen($name) < 2 || strlen($name) > 100) {
        $errors['name'] = 'Category name must be between 2 and 100 characters.';
    }
    if (!isSafeCategoryColor($color)) {
        $errors['color'] = 'Color must be a valid hex code (e.g., #6366F1).';
    }
    if ($icon === 'tag' && !preg_match('/^[a-zA-Z0-9_-]+$/', (string) ($input['icon'] ?? 'tag'))) {
        $errors['icon'] = 'Invalid icon name.';
    }
    if (!empty($errors)) {
        sendError('Please fix the errors below.', $errors, 422);
    }

    $pdo = getConnection();
    $checkStmt = $pdo->prepare("SELECT id, type FROM categories WHERE id = ? AND user_id = ? LIMIT 1");
    $checkStmt->execute([$id, $userId]);
    $existing = $checkStmt->fetch();
    if (!$existing) {
        sendError('Category not found.', null, 404);
    }

    $dupStmt = $pdo->prepare("SELECT id FROM categories WHERE user_id = ? AND name = ? AND type = ? AND id != ? LIMIT 1");
    $dupStmt->execute([$userId, $name, $existing['type'], $id]);
    if ($dupStmt->fetch()) {
        sendError('A category with this name already exists.', null, 409);
    }

    $stmt = $pdo->prepare("UPDATE categories SET name = ?, icon = ?, color = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$name, $icon, $color, $id, $userId]);

    $fetchStmt = $pdo->prepare("SELECT id, user_id, name, type, icon, color, created_at FROM categories WHERE id = ? AND user_id = ?");
    $fetchStmt->execute([$id, $userId]);
    $category = $fetchStmt->fetch();

    sendJson($category, 'Category updated successfully.');
}

if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    $moveToId = intval($_GET['move_to_id'] ?? 0);

    if ($id <= 0) {
        sendError('Invalid category ID.', null, 400);
    }
    requireApiCsrf();

    $pdo = getConnection();
    $checkStmt = $pdo->prepare("SELECT id, type FROM categories WHERE id = ? AND user_id = ? LIMIT 1");
    $checkStmt->execute([$id, $userId]);
    $cat = $checkStmt->fetch();
    if (!$cat) {
        sendError('Category not found.', null, 404);
    }

    if ($moveToId > 0) {
        $targetStmt = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND user_id = ? AND type = ? LIMIT 1");
        $targetStmt->execute([$moveToId, $userId, $cat['type']]);
        if (!$targetStmt->fetch()) {
            sendError('Target category not found or type mismatch.', null, 400);
        }
        $reassignStmt = $pdo->prepare("UPDATE transactions SET category_id = ? WHERE category_id = ? AND user_id = ?");
        $reassignStmt->execute([$moveToId, $id, $userId]);
    } else {

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE category_id = ? AND user_id = ?");
        $countStmt->execute([$id, $userId]);
        $remaining = (int) $countStmt->fetchColumn();
        if ($remaining > 0) {
            sendError(
                "This category still has $remaining transaction(s). Move them to another category first.",
                ['move_to_id' => 'Select a category to move existing transactions into.'],
                409
            );
        }
    }

    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    sendJson(['id' => $id], 'Category deleted successfully.');
}

sendError('Method not allowed. Use GET, POST, PUT, or DELETE.', null, 405);
