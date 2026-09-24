<?php


function createNotification(PDO $pdo, int $userId, string $type, string $title, string $message, ?string $link = null): void
{
    if ($userId <= 0) {
        return;
    }
    if (!in_array($type, ['over_budget', 'info', 'login'], true)) {
        $type = 'info';
    }
    $title = trim($title) !== '' ? mb_substr(trim($title), 0, 200) : 'Notification';
    $message = trim($message) !== '' ? trim($message) : $title;
    if ($link !== null) {
        $link = mb_substr(trim($link), 0, 255);
        if ($link === '') {
            $link = null;
        } elseif (!preg_match('#^(\\?page=[a-z0-9_-]+.*|/.*)$#i', $link)) {
            
            $link = null;
        }
    }

    $manageTransaction = !$pdo->inTransaction();
    try {
        if ($manageTransaction) {
            $pdo->beginTransaction();
        }

        $insert = $pdo->prepare('INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)');
        $insert->execute([$userId, $type, $title, $message, $link]);

        $countStmt = $pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ?');
        $countStmt->execute([$userId]);
        $excess = (int) $countStmt->fetchColumn() - 30;
        if ($excess > 0) {
            $excess = min($excess, 1000);
            $prune = $pdo->prepare("DELETE FROM notifications WHERE user_id = ? ORDER BY created_at ASC, id ASC LIMIT $excess");
            $prune->execute([$userId]);
        }

        if ($manageTransaction) {
            $pdo->commit();
        }
    } catch (Throwable $e) {
        if ($manageTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        epLog('Notification create failed: ' . $e->getMessage());
    }
}

function recordLoginNotification(PDO $pdo, int $userId): void
{
    try {
        $recent = $pdo->prepare(
            "SELECT id FROM notifications WHERE user_id = ? AND type = 'login' AND created_at >= (NOW() - INTERVAL 15 MINUTE) LIMIT 1"
        );
        $recent->execute([$userId]);
        if ($recent->fetch()) {
            return;
        }
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');
        $device = getDeviceCategory($ua);
        $browser = getBrowserName($ua);
        $when = date('j M Y, g:i A');
        $message = "Signed in on {$when} from {$device} · {$browser}. If this was not you, change your password.";
        createNotification($pdo, $userId, 'login', 'New login', $message, '?page=profile');
    } catch (Throwable $e) {
        epLog('Login notification failed: ' . $e->getMessage());
    }
}
