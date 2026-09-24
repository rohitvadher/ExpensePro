<?php


require_once __DIR__ . '/../database/repositories/CategoryRepository.php';

class CategoryService
{
    public function __construct(private PDO $pdo, private CategoryRepository $repo) {}

    public static function for(PDO $pdo): self
    {
        return new self($pdo, new CategoryRepository($pdo));
    }

    public function create(int $userId, array $input): array
    {
        $errors = validateCategoryInput($input, true);
        if ($errors !== []) {
            sendError('Please fix the errors below.', $errors, 422);
        }
        $name = sanitize((string) $input['name']);
        $type = (string) $input['type'];
        $icon = safeCategoryIcon($input['icon'] ?? 'tag');
        $color = (string) ($input['color'] ?? '#6366F1');

        if ($this->repo->findDuplicate($userId, $name, $type)) {
            sendError('A category with this name already exists.', null, 409);
        }
        $newId = $this->repo->insert($userId, $name, $type, $icon, $color);
        $row = $this->repo->findOwned($newId, $userId);
        if (!$row) {
            sendError('Category could not be loaded after saving.', null, 500);
        }
        return $row;
    }
}
