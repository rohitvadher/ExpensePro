<?php

require_once __DIR__ . '/../includes/api.php';

$userId = requireApiAuth();
$method = requireApiMethod(['GET', 'POST']);

if ($method === 'GET') {
    $pdo = getConnection();

    $lastCheck = $_SESSION['notification_last_check'] ?? 0;
    if (time() - $lastCheck > 1800) {
        generateSmartNotifications($pdo, $userId);
        $_SESSION['notification_last_check'] = time();
    }

    $limit = min(50, max(1, intval($_GET['limit'] ?? 20)));
    $unread = isset($_GET['unread_only']) && $_GET['unread_only'] === '1';

    $sql = 'SELECT id, type, title, message, link, is_read, created_at FROM notifications WHERE user_id = ?';
    if ($unread) { $sql .= ' AND is_read = 0'; }
    $sql .= ' ORDER BY created_at DESC LIMIT ?';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId, $limit]);
    $notifications = $stmt->fetchAll();

    $countStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
    $countStmt->execute([$userId]);
    $unreadCount = (int) $countStmt->fetchColumn();

    sendJson([
        'notifications' => array_map(function ($n) {
            return ['id' => (int) $n['id'], 'type' => $n['type'], 'title' => $n['title'], 'message' => $n['message'], 'link' => $n['link'], 'is_read' => (bool) $n['is_read'], 'created_at' => $n['created_at']];
        }, $notifications),
        'unread_count' => $unreadCount
    ], 'Notifications retrieved successfully.');
}

if ($method === 'POST') {
    $input = requireJsonBody();
    requireApiCsrf();
    require_once __DIR__ . '/../backend/database/repositories/NotificationRepository.php';

    $action = $input['action'] ?? '';

    if ($action === 'mark_read') {
        $pdo = getConnection();
        $repo = new NotificationRepository($pdo);
        $ids = $input['ids'] ?? [];

        if ($ids === 'all' || (is_array($ids) && in_array('all', $ids, true))) {
            $repo->markAllRead($userId);
        } elseif (is_array($ids) && count($ids) > 0) {
            $repo->markIdsRead($userId, $ids);
        } else {
            $id = intval($input['id'] ?? 0);
            if ($id > 0) {
                $repo->markIdsRead($userId, [$id]);
            }
        }

        $unreadCount = $repo->unreadCount($userId);
        sendJson(['unread_count' => $unreadCount], 'Notifications marked as read.');
    }

    if ($action === 'generate') {
        $pdo = getConnection();
        generateSmartNotifications($pdo, $userId);
        $_SESSION['notification_last_check'] = time();
        sendJson(null, 'Notifications refreshed.');
    }

    sendError('Invalid action.', null, 400);
}

function generateSmartNotifications(PDO $pdo, int $userId): void
{
    $today = date('Y-m-d');

    require_once __DIR__ . '/../backend/database/repositories/NotificationRepository.php';
    $repo = new NotificationRepository($pdo);

    try {
        $budgetStmt = $pdo->prepare("SELECT b.id, b.amount, b.period, b.category_id, c.name AS category_name FROM budgets b LEFT JOIN categories c ON c.id = b.category_id AND c.user_id = b.user_id WHERE b.user_id = ?");
        $budgetStmt->execute([$userId]);
        $budgets = $budgetStmt->fetchAll();
        foreach ($budgets as $budget) {
            try {
                $range = getBudgetPeriodRange($budget['period']);
                $params = [$userId];
                $catSql = '';
                if ($budget['category_id']) { $catSql = ' AND category_id = ?'; $params[] = (int) $budget['category_id']; }
                $params[] = $range['start_date']; $params[] = $range['end_date'];
                $spentStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = ? AND type = 'expense'{$catSql} AND date BETWEEN ? AND ?");
                $spentStmt->execute($params);
                $spent = (float) $spentStmt->fetchColumn();
                if ($spent > (float) $budget['amount']) {
                    $catLabel = $budget['category_name'] ? $budget['category_name'] : 'Overall';
                    $title = "Over Budget: {$catLabel}";
                    if (!$repo->hasUnreadToday($userId, 'over_budget', $title, $today)) {
                        $overBy = number_format($spent - (float) $budget['amount'], 2);
                        $message = "Your {$budget['period']} budget for {$catLabel} is exceeded by {$overBy}.";
                        createNotification($pdo, $userId, 'over_budget', $title, $message, '?page=budgets');
                    }
                }
            } catch (Throwable $budgetError) {
                epLog('Notification budget row failed: ' . $budgetError->getMessage());
                continue;
            }
        }
    } catch (Throwable $e) { epLog('Notification budget check failed: ' . $e->getMessage()); }
}
