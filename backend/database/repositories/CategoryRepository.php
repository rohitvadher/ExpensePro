<?php


class CategoryRepository
{
    public function __construct(private PDO $pdo) {}

    public function findOwned(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, user_id, name, type, icon, color, created_at FROM categories WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findDuplicate(int $userId, string $name, string $type, ?int $excludeId = null): ?array
    {
        if ($excludeId !== null) {
            $stmt = $this->pdo->prepare('SELECT id FROM categories WHERE user_id = ? AND name = ? AND type = ? AND id != ? LIMIT 1');
            $stmt->execute([$userId, $name, $type, $excludeId]);
        } else {
            $stmt = $this->pdo->prepare('SELECT id FROM categories WHERE user_id = ? AND name = ? AND type = ? LIMIT 1');
            $stmt->execute([$userId, $name, $type]);
        }
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insert(int $userId, string $name, string $type, string $icon, string $color): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO categories (user_id, name, type, icon, color) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $name, $type, $icon, $color]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateOwned(int $id, int $userId, string $name, string $icon, string $color): void
    {
        $stmt = $this->pdo->prepare('UPDATE categories SET name = ?, icon = ?, color = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$name, $icon, $color, $id, $userId]);
    }

    public function deleteOwned(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM categories WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }

    public function countTransactions(int $categoryId, int $userId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM transactions WHERE category_id = ? AND user_id = ?');
        $stmt->execute([$categoryId, $userId]);
        return (int) $stmt->fetchColumn();
    }

    public function reassignTransactions(int $fromId, int $toId, int $userId): void
    {
        $stmt = $this->pdo->prepare('UPDATE transactions SET category_id = ? WHERE category_id = ? AND user_id = ?');
        $stmt->execute([$toId, $fromId, $userId]);
    }
}
