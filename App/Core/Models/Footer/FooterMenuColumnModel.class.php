<?php

declare(strict_types=1);

class FooterMenuColumnModel extends AbstractSaveModel
{
    public function getAllActiveColumns(): array
    {
        $params = [
            'is_active' => true,
            'ORDER BY' => 'sort_order ASC',
        ];
        $result = $this->all($params);
        return $result->exists() ? $result->asArray() : [];
    }

    public function getAllColumns(): array
    {
        $params = [
            'ORDER BY' => 'sort_order ASC',
        ];
        $result = $this->all($params);
        return $result->exists() ? $result->asArray() : [];
    }

    public function getColumn(null|int|string $id = null): array
    {
        if ($id === null) {
            return [];
        }
        $result = $this->getById($id);
        if ($result->isSuccess()) {
            return $result->asArray();
        }
        return [];
    }

    public function createColumn(array $data): ModelResult
    {
        try {
            $result = $this->insert($data);
            if ($result->isSuccess()) {
                $insertedId = $result->getLastInsertId();
                return ModelResult::success(
                    [
                        'inserted_id' => $insertedId,
                        'column' => $result->asArray(),
                    ],
                    'Column created successfully',
                );
            }

            return ModelResult::error('Failed to create column');
        } catch (Throwable $e) {
            return ModelResult::fromException($e, 'Database error while creating column');
        }
    }

    public function updateColumn(array $data): ModelResult
    {
        try {
            $result = parent::save($data);
            if ($result->isSuccess()) {
                return ModelResult::success(
                    [
                        'affected_rows' => $result->getAffectedRows(),
                    ],
                    'Column updated successfully',
                );
            }
            return ModelResult::error('Failed to update column');
        } catch (Throwable $e) {
            return ModelResult::fromException($e, 'Database error while updating column');
        }
    }

    public function deleteColumn(int $id): ModelResult
    {
        try {
            $result = $this->delete($id);
            if ($result->isSuccess()) {
                return ModelResult::success(null, 'Column deleted successfully');
            }

            return ModelResult::error('Failed to delete column');
        } catch (Throwable $e) {
            return ModelResult::fromException($e, 'Database error while deleting column');
        }
    }

    public function toggleActive(int $id, bool $isActive): ModelResult
    {
        try {
            $result = $this->updateColumn([
                'id' => $id,
                'is_active' => $isActive,
            ]);

            if ($result->isSuccess()) {
                return ModelResult::success(
                    null,
                    $isActive ? 'Column activated' : 'Column deactivated',
                );
            }

            return ModelResult::error('Failed to toggle column status');
        } catch (Throwable $e) {
            return ModelResult::fromException($e, 'Database error while toggling column status');
        }
    }

    public function reorderColumns(array $columns): ModelResult
    {
        if (empty($columns)) {
            return ModelResult::error('No columns to reorder');
        }

        $data = [];
        foreach ($columns as $sortOrder => $columnId) {
            $data[] = [
                'id' => $columnId,
                'sort_order' => $sortOrder,
            ];
        }
        return $this->updateColumn($data);
    }

    public function getColumnByKey(string $key): ?array
    {
        $params = [
            'column_key' => $key,
        ];
        $result = $this->first($params);
        return $result->exists() ? $result->asArray() : null;
    }

    public function columnExists(string $key): bool
    {
        $params = [
            'column_key' => $key,
        ];
        $result = $this->count($params);
        return $result > 0;
    }

    #[Override]
    protected function validateData(array $data): void
    {
        if (isset($data['title']) && empty(trim($data['title']))) {
            throw new InvalidArgumentException('Column title cannot be empty');
        }

        if (isset($data['column_key']) && empty(trim($data['column_key']))) {
            throw new InvalidArgumentException('Column key cannot be empty');
        }

        if (isset($data['column_key']) && !preg_match('/^[a-z0-9_]+$/', $data['column_key'])) {
            throw new InvalidArgumentException('Column key must contain only lowercase letters, numbers, and underscores');
        }

        if (isset($data['sort_order']) && $data['sort_order'] < 0) {
            throw new InvalidArgumentException('Sort order must be a non-negative integer');
        }
    }

    #[Override]
    protected function generateMissingFields(array $data): array
    {
        // Generate column_key from title if not provided
        if (!isset($data['column_key']) && isset($data['title'])) {
            $data['column_key'] = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($data['title'])));
        }

        if (!isset($data['sort_order'])) {
            $data['sort_order'] = 0;
        }

        if (!isset($data['is_active'])) {
            $data['is_active'] = 1;
        }

        return $data;
    }
}