<?php


require_once __DIR__ . '/../database/repositories/TransactionRepository.php';

class TransactionService
{
    public function __construct(private PDO $pdo, private TransactionRepository $repo) {}

    public static function for(PDO $pdo): self
    {
        return new self($pdo, new TransactionRepository($pdo));
    }

    public function buildFilter(int $userId, array $query): array
    {
        $where = ['t.user_id = ?'];
        $params = [$userId];

        $type = $query['type'] ?? '';
        if ($type === 'income' || $type === 'expense') {
            $where[] = 't.type = ?';
            $params[] = $type;
        }
        $search = trim((string) ($query['search'] ?? ''));
        if ($search !== '') {
            $where[] = 't.description LIKE ?';
            $params[] = '%' . addcslashes($search, '%_') . '%';
        }
        $dateFrom = (string) ($query['date_from'] ?? '');
        $dateTo = (string) ($query['date_to'] ?? '');
        if (validateDate($dateFrom)) {
            $where[] = 't.date >= ?';
            $params[] = $dateFrom;
        }
        if (validateDate($dateTo)) {
            $where[] = 't.date <= ?';
            $params[] = $dateTo;
        }
        $categoryId = (int) ($query['category_id'] ?? 0);
        if ($categoryId > 0) {
            $where[] = 't.category_id = ?';
            $params[] = $categoryId;
        }
        return [implode(' AND ', $where), $params];
    }

    public function create(int $userId, array $input): array
    {
        $errors = validateTransactionInput($input);
        if ($errors !== []) {
            sendError('Please fix the errors below.', $errors, 422);
        }
        $type = $input['type'];
        $categoryId = (int) $input['category_id'];
        $amount = moneyRound((float) $input['amount']);
        $date = trim((string) $input['date']);
        $description = trim((string) ($input['description'] ?? ''));
        $description = $description !== '' ? $description : null;

        requireOwnedCategory($this->pdo, $userId, $categoryId, $type);

        $dup = $this->repo->findDuplicate($userId, $categoryId, $type, $amount, $date, $description);
        if ($dup) {
            sendError(
                'A duplicate transaction already exists. Please edit the existing one instead.',
                ['duplicate_id' => (int) $dup['id'], 'duplicate_date' => $dup['created_at']],
                409
            );
        }
        $newId = $this->repo->insert($userId, $categoryId, $type, $amount, $description, $date);
        $row = $this->repo->findOwned($newId, $userId);
        if (!$row) {
            sendError('Transaction could not be loaded after saving.', null, 500);
        }
        return presentCategoryFields($row);
    }
}
