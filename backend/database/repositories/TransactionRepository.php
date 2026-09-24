<?php


class TransactionRepository
{
    public function __construct(private PDO $pdo) {}

    public function findOwned(int $id, int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT t.id, t.user_id, t.category_id, t.type, t.amount, t.description,
                    t.date, t.created_at, t.updated_at,
                    c.name AS category_name, c.icon AS category_icon, c.color AS category_color
             FROM transactions t
             JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id
             WHERE t.id = ? AND t.user_id = ?
             LIMIT 1'
        );
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function countForFilters(int $userId, string $whereClause, array $params): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM transactions t WHERE $whereClause");
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row ? (int) $row['total'] : 0;
    }

    
    public function listForFilters(int $userId, string $whereClause, array $params, int $limit, int $offset): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT t.id, t.user_id, t.category_id, t.type, t.amount, t.description,
                    t.date, t.created_at, t.updated_at,
                    c.name AS category_name, c.icon AS category_icon, c.color AS category_color
             FROM transactions t
             JOIN categories c ON t.category_id = c.id AND c.user_id = t.user_id
             WHERE $whereClause
             ORDER BY t.date DESC, t.id DESC
             LIMIT $limit OFFSET $offset"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findDuplicate(int $userId, int $categoryId, string $type, float $amount, string $date, ?string $description): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, created_at FROM transactions
             WHERE user_id = ? AND category_id = ? AND type = ?
                   AND amount = ? AND date = ?
                   AND COALESCE(description, \'\') = COALESCE(?, \'\')
             LIMIT 1'
        );
        $stmt->execute([$userId, $categoryId, $type, $amount, $date, $description]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function insert(int $userId, int $categoryId, string $type, float $amount, ?string $description, string $date): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO transactions (user_id, category_id, type, amount, description, date) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $categoryId, $type, $amount, $description, $date]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateOwned(int $id, int $userId, string $type, int $categoryId, float $amount, ?string $description, string $date): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE transactions SET type = ?, category_id = ?, amount = ?, description = ?, date = ? WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$type, $categoryId, $amount, $description, $date, $id, $userId]);
    }

    public function existsOwned(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('SELECT id FROM transactions WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $userId]);
        return (bool) $stmt->fetch();
    }

    public function deleteOwned(int $id, int $userId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }

    public function sumSpent(int $userId, ?int $categoryId, string $start, string $end): float
    {
        if ($categoryId !== null && $categoryId > 0) {
            $stmt = $this->pdo->prepare(
                "SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = ? AND type = 'expense' AND category_id = ? AND date BETWEEN ? AND ?"
            );
            $stmt->execute([$userId, $categoryId, $start, $end]);
        } else {
            $stmt = $this->pdo->prepare(
                "SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = ? AND type = 'expense' AND date BETWEEN ? AND ?"
            );
            $stmt->execute([$userId, $start, $end]);
        }
        return (float) $stmt->fetchColumn();
    }
}
