<?php


class BudgetRepository
{
    public function __construct(private PDO $pdo) {}

    public function findOwned(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, user_id, category_id, amount, period, start_date, end_date FROM budgets WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findDuplicate(int $userId, string $period, ?int $categoryId, ?int $excludeId = null): ?array
    {
        if ($categoryId === null) {
            $sql = 'SELECT id FROM budgets WHERE user_id = ? AND period = ? AND category_id IS NULL';
            $params = [$userId, $period];
        } else {
            $sql = 'SELECT id FROM budgets WHERE user_id = ? AND period = ? AND category_id = ?';
            $params = [$userId, $period, $categoryId];
        }
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $sql .= ' LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insert(int $userId, ?int $categoryId, float $amount, string $period, string $start, string $end): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO budgets (user_id, category_id, amount, period, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $categoryId, $amount, $period, $start, $end]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateOwned(int $id, int $userId, ?int $categoryId, float $amount, string $period, string $start, string $end): void
    {
        $stmt = $this->pdo->prepare('UPDATE budgets SET category_id = ?, amount = ?, period = ?, start_date = ?, end_date = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$categoryId, $amount, $period, $start, $end, $id, $userId]);
    }

    public function deleteOwned(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM budgets WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }
}
