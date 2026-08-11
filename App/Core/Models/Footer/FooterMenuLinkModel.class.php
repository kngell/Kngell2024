<?php

declare(strict_types=1);

class FooterMenuLinkModel extends AbstractSaveModel
{
    public function getColumnLinks(int|string $columnId): array
    {
        $params = [
            'column_id' => $columnId,
            'is_active' => true,
            'ORDER BY' => 'sort_order ASC',
        ];
        $result = $this->one($params);
        return $result->exists() ? $result->asArray() : [];
    }

    public function getItem(int $id): ?array
    {
        $result = $this->find($id);
        if ($result->isSuccess()) {
            return $result->asArray();
        }
        return null;
    }

    public function createItem(array $data): ModelResult
    {
        try {
            $result = $this->insert($data);
            if ($result->isSuccess()) {
                $insertedId = $result->getLastInsertId();
                return ModelResult::success(
                    [
                        'inserted_id' => $insertedId,
                        'item' => $result->asArray(),
                    ],
                    'Menu item created successfully',
                );
            }

            return ModelResult::error('Failed to create menu item');
        } catch (Throwable $e) {
            return ModelResult::fromException($e, 'Database error while creating menu item');
        }
    }

    public function updateItem(array $data): ModelResult
    {
        try {
            $result = parent::save($data);
            if ($result->isSuccess()) {
                return ModelResult::success(
                    [
                        'affected_rows' => $result->getAffectedRows(),
                    ],
                    'Menu item updated successfully',
                );
            }
            return ModelResult::error('Failed to update menu item');
        } catch (Throwable $e) {
            return ModelResult::fromException($e, 'Database error while updating menu item');
        }
    }

    public function deleteItem(int $id): ModelResult
    {
        try {
            $result = $this->delete($id);
            if ($result->isSuccess()) {
                return ModelResult::success(null, 'Menu item deleted successfully');
            }

            return ModelResult::error('Failed to delete menu item');
        } catch (Throwable $e) {
            return ModelResult::fromException($e, 'Database error while deleting menu item');
        }
    }

    public function reorderItems(int $columnId, array $items): ModelResult
    {
        if (empty($items)) {
            return ModelResult::error('No items to reorder');
        }

        // Build bulk update data
        $data = [];
        foreach ($items as $sortOrder => $itemId) {
            $data[] = [
                'id' => $itemId,
                'column_id' => $columnId,
                'sort_order' => $sortOrder,
            ];
        }
        return $this->updateItem($data);
    }

    public function toggleActive(int $id, bool $isActive): ModelResult
    {
        try {
            $result = $this->updateItem([
                'id' => $id,
                'is_active' => $isActive,
            ]);

            if ($result->isSuccess()) {
                return ModelResult::success(
                    null,
                    $isActive ? 'Menu item activated' : 'Menu item deactivated',
                );
            }

            return ModelResult::error('Failed to toggle menu item status');
        } catch (Throwable $e) {
            return ModelResult::fromException($e, 'Database error while toggling menu item status');
        }
    }

    public function getItemsByColumn(int $columnId): array
    {
        $params = [
            'column_id' => $columnId,
            'ORDER BY' => 'sort_order ASC',
        ];
        $result = $this->all($params);
        return $result->exists() ? $result->asArray() : [];
    }

    public function getActiveItemsByColumn(int $columnId): array
    {
        $params = [
            'column_id' => $columnId,
            'is_active' => true,
            'ORDER BY' => 'sort_order ASC',
        ];
        $result = $this->all($params);
        return $result->exists() ? $result->asArray() : [];
    }

    public function getItemCount(int $columnId): int
    {
        $params = [
            'column_id' => $columnId,
        ];
        return $this->count($params);
    }

    public function getActiveItemCount(int $columnId): int
    {
        $params = [
            'column_id' => $columnId,
            'is_active' => true,
        ];
        return $this->count($params);
    }

    public function deleteItemsByColumn(int $columnId): ModelResult
    {
        try {
            $params = ['column_id' => $columnId];
            $result = $this->delete($params);

            if ($result->isSuccess()) {
                return ModelResult::success(
                    ['deleted_count' => $result->getAffectedRows()],
                    'All menu items for column deleted successfully',
                );
            }

            return ModelResult::error('Failed to delete menu items');
        } catch (Throwable $e) {
            return ModelResult::fromException($e, 'Database error while deleting menu items');
        }
    }

    #[Override]
    protected function validateData(array $data): void
    {
        if (isset($data['title']) && empty(trim($data['title']))) {
            throw new InvalidArgumentException('Menu item title cannot be empty');
        }

        if (isset($data['column_id']) && is_int($data['column_id']) && $data['column_id'] <= 0) {
            throw new InvalidArgumentException('Invalid column ID');
        }

        if (isset($data['target']) && !in_array($data['target'], ['_self', '_blank'], true)) {
            $data['target'] = '_self';
        }

        if (isset($data['sort_order']) && $data['sort_order'] < 0) {
            throw new InvalidArgumentException('Sort order must be a non-negative integer');
        }
    }

    #[Override]
    protected function generateMissingFields(array $data): array
    {
        // Set default values for missing fields
        if (!isset($data['target'])) {
            $data['target'] = '_self';
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