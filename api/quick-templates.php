<?php

require_once __DIR__ . '/../includes/api.php';

$userId = requireApiAuth();
$method = requireApiMethod(['GET', 'POST', 'PUT', 'DELETE']);

if ($method === 'GET') {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT qt.id, qt.user_id, qt.name, qt.type, qt.amount, qt.category_id, qt.description, qt.usage_count, qt.created_at, c.name AS category_name, c.icon AS category_icon, c.color AS category_color FROM quick_templates qt LEFT JOIN categories c ON qt.category_id = c.id AND c.user_id = qt.user_id WHERE qt.user_id = ? ORDER BY qt.usage_count DESC, qt.name ASC");
    $stmt->execute([$userId]);
    sendJson(array_map('presentCategoryFields', $stmt->fetchAll()), 'Quick templates retrieved successfully.');
}

if ($method === 'POST') {
    $input = requireJsonBody();
    requireApiCsrf();

    $action = $input['action'] ?? '';

    if ($action === 'use') {
        $templateId = intval($input['id'] ?? 0);
        if ($templateId <= 0) {
            sendError('Invalid template ID.', null, 400);
        }
        $pdo = getConnection();
        $checkStmt = $pdo->prepare("SELECT id, name, type, amount, category_id, description FROM quick_templates WHERE id = ? AND user_id = ? LIMIT 1");
        $checkStmt->execute([$templateId, $userId]);
        $template = $checkStmt->fetch();
        if (!$template) {
            sendError('Template not found.', null, 404);
        }
        $updateStmt = $pdo->prepare("UPDATE quick_templates SET usage_count = usage_count + 1 WHERE id = ? AND user_id = ?");
        $updateStmt->execute([$templateId, $userId]);
        sendJson(['id' => (int) $template['id'], 'name' => $template['name'], 'type' => $template['type'], 'amount' => (float) $template['amount'], 'category_id' => (int) $template['category_id'], 'description' => $template['description']], 'Template applied successfully.');
    }

    $name = sanitize($input['name'] ?? '');
    $type = $input['type'] ?? '';
    $amount = $input['amount'] ?? '';
    $categoryId = intval($input['category_id'] ?? 0);
    $description = trim($input['description'] ?? '');

    $errors = [];
    if (strlen($name) < 2 || strlen($name) > 100) { $errors['name'] = 'Template name must be between 2 and 100 characters.'; }
    if ($type !== 'income' && $type !== 'expense') { $errors['type'] = 'Type must be "income" or "expense".'; }
    if (!validateAmount($amount)) { $errors['amount'] = 'Amount must be a positive number.'; }
    if ($categoryId <= 0) { $errors['category_id'] = 'Please select a category.'; }
    if (!empty($errors)) { sendError('Please fix the errors below.', $errors, 422); }

    $pdo = getConnection();
    requireOwnedCategory($pdo, $userId, $categoryId, $type);

    $stmt = $pdo->prepare("INSERT INTO quick_templates (user_id, name, type, amount, category_id, description, usage_count) VALUES (?, ?, ?, ?, ?, ?, 0)");
    $stmt->execute([$userId, $name, $type, $amount, $categoryId, $description ?: null]);
    $newId = $pdo->lastInsertId();

    $fetchStmt = $pdo->prepare("SELECT qt.id, qt.user_id, qt.name, qt.type, qt.amount, qt.category_id, qt.description, qt.usage_count, qt.created_at, c.name AS category_name, c.icon AS category_icon, c.color AS category_color FROM quick_templates qt LEFT JOIN categories c ON qt.category_id = c.id AND c.user_id = qt.user_id WHERE qt.id = ? AND qt.user_id = ?");
    $fetchStmt->execute([$newId, $userId]);
    $template = $fetchStmt->fetch();

    sendJson(presentCategoryFields($template), 'Template created successfully.', 201);
}

if ($method === 'PUT') {
    $input = requireJsonBody();
    requireApiCsrf();

    $id = intval($input['id'] ?? 0);
    $name = sanitize($input['name'] ?? '');
    $type = $input['type'] ?? '';
    $amount = $input['amount'] ?? '';
    $categoryId = intval($input['category_id'] ?? 0);
    $description = trim($input['description'] ?? '');

    if ($id <= 0) { sendError('Invalid template ID.', null, 400); }

    $errors = [];
    if (strlen($name) < 2 || strlen($name) > 100) { $errors['name'] = 'Template name must be between 2 and 100 characters.'; }
    if ($type !== 'income' && $type !== 'expense') { $errors['type'] = 'Type must be "income" or "expense".'; }
    if (!validateAmount($amount)) { $errors['amount'] = 'Amount must be a positive number.'; }
    if ($categoryId <= 0) { $errors['category_id'] = 'Please select a category.'; }
    if (!empty($errors)) { sendError('Please fix the errors below.', $errors, 422); }

    $pdo = getConnection();
    $checkStmt = $pdo->prepare("SELECT id FROM quick_templates WHERE id = ? AND user_id = ? LIMIT 1");
    $checkStmt->execute([$id, $userId]);
    if (!$checkStmt->fetch()) {
        sendError('Template not found.', null, 404);
    }
    requireOwnedCategory($pdo, $userId, $categoryId, $type);

    $stmt = $pdo->prepare("UPDATE quick_templates SET name = ?, type = ?, amount = ?, category_id = ?, description = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$name, $type, $amount, $categoryId, $description ?: null, $id, $userId]);

    $fetchStmt = $pdo->prepare("SELECT qt.id, qt.user_id, qt.name, qt.type, qt.amount, qt.category_id, qt.description, qt.usage_count, qt.created_at, c.name AS category_name, c.icon AS category_icon, c.color AS category_color FROM quick_templates qt LEFT JOIN categories c ON qt.category_id = c.id AND c.user_id = qt.user_id WHERE qt.id = ? AND qt.user_id = ?");
    $fetchStmt->execute([$id, $userId]);
    $template = $fetchStmt->fetch();

    sendJson(presentCategoryFields($template), 'Template updated successfully.');
}

if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if ($id <= 0) { sendError('Invalid template ID.', null, 400); }
    requireApiCsrf();

    $pdo = getConnection();
    $checkStmt = $pdo->prepare("SELECT id FROM quick_templates WHERE id = ? AND user_id = ? LIMIT 1");
    $checkStmt->execute([$id, $userId]);
    if (!$checkStmt->fetch()) {
        sendError('Template not found.', null, 404);
    }
    $stmt = $pdo->prepare("DELETE FROM quick_templates WHERE id = ? AND user_id = ?");
    $stmt->execute([$id, $userId]);
    sendJson(['id' => $id], 'Template deleted successfully.');
}

sendError('Method not allowed. Use GET, POST, PUT, or DELETE.', null, 405);
