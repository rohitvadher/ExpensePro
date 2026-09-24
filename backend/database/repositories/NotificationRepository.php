<?php


class NotificationRepository
{
    public function __construct(private PDO $pdo) {}

    public function markAllRead(int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        $stmt->execute([$userId]);
    }

    public function markIdsRead(int $userId, array $ids): void
    {
        $clean = [];
        foreach ($ids as $id) {
            if (validatePositiveId($id) || (is_numeric($id) && (int) $id > 0)) {
                $clean[] = (int) $id;
            }
        }
        $clean = array_values(array_unique($clean));
        $clean = array_slice($clean, 0, 100);
        if ($clean === []) {
            return;
        }
        $placeholders = implode(',', array_fill(0, count($clean), '?'));
        $params = array_merge($clean, [$userId]);
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id IN ($placeholders) AND user_id = ?");
        $stmt->execute($params);
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    public function hasUnreadToday(int $userId, string $type, string $title, string $today): bool
    {
        
        $stmt = $this->pdo->prepare(
            'SELECT id FROM notifications WHERE user_id = ? AND type = ? AND title = ? AND created_at >= ? AND created_at < ? + INTERVAL 1 DAY AND is_read = 0 LIMIT 1'
        );
        $stmt->execute([$userId, $type, $title, $today, $today]);
        return (bool) $stmt->fetch();
    }
}
