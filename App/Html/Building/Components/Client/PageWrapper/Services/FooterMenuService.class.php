<?php

declare(strict_types=1);

class FooterMenuService
{
    private const CACHE_KEY = 'footer_menu_structure';
    private const CACHE_TTL = 86400; // 24 hours

    private HtmlSectionCacheManager $cache;

    public function __construct(
        private FooterMenuColumnModel $columnModel,
        private FooterMenuLinkModel $linkModel,
        private FooterMenuCacheManagerFactory $cacheFactory,
    ) {
        $this->cache = $cacheFactory->create();
    }

    public function getFooterMenu(): array
    {
        return $this->cache->remember(self::CACHE_KEY, function () {
            return $this->loadFromDatabase();
        }, self::CACHE_TTL);
    }

    public function getFooterMenuColumn(int $columnId): ?array
    {
        $cacheKey = $this->getColumnCacheKey($columnId);

        return $this->cache->remember($cacheKey, function () use ($columnId) {
            return $this->columnModel->getColumn($columnId);
        }, self::CACHE_TTL);
    }

    public function getFooterMenuItems(int $columnId): array
    {
        $cacheKey = $this->getItemsCacheKey($columnId);

        return $this->cache->remember($cacheKey, function () use ($columnId) {
            return $this->linkModel->getColumnLinks($columnId);
        }, self::CACHE_TTL);
    }

    public function createColumn(array $data): ModelResult
    {
        $result = $this->columnModel->createColumn($data);

        if ($result->isSuccess()) {
            $this->clearCache();
        }

        return $result;
    }

    public function updateColumn(int $id, array $data): ModelResult
    {
        $data['id'] = $id;
        $result = $this->columnModel->updateColumn($data);

        if ($result->isSuccess()) {
            $this->invalidateColumnCache($id);
        }

        return $result;
    }

    public function deleteColumn(int $id): ModelResult
    {
        $result = $this->columnModel->deleteColumn($id);

        if ($result->isSuccess()) {
            $this->clearCache();
        }

        return $result;
    }

    public function reorderColumns(array $sortedIds): ModelResult
    {
        if (empty($sortedIds)) {
            return ModelResult::error('No columns to reorder');
        }
        $data = [];
        foreach ($sortedIds as $sortOrder => $columnId) {
            $data[] = [
                'id' => $columnId,
                'sort_order' => $sortOrder,
            ];
        }
        $result = $this->columnModel->updateColumn($data);
        if ($result->isSuccess()) {
            $this->clearCache();
        }
        return $result;
    }

    public function createItem(array $data): ModelResult
    {
        $result = $this->linkModel->createItem($data);

        if ($result->isSuccess()) {
            $this->invalidateItemsCache($data['column_id']);
        }
        return $result;
    }

    public function updateItem(int $id, array $data): ModelResult
    {
        $item = $this->linkModel->getItem($id);
        $data['id'] = $id;
        $result = $this->linkModel->updateItem($data);

        if ($result->isSuccess() && $item) {
            $this->invalidateItemsCache($item['column_id']);
        }

        return $result;
    }

    public function deleteItem(int $id): ModelResult
    {
        $item = $this->linkModel->getItem($id);
        $result = $this->linkModel->deleteItem($id);

        if ($result->isSuccess() && $item) {
            $this->invalidateItemsCache($item['column_id']);
        }

        return $result;
    }

    public function reorderItems(int $columnId, array $sortedIds): ModelResult
    {
        if (empty($sortedIds)) {
            return ModelResult::error('No items to reorder');
        }

        $data = [];
        foreach ($sortedIds as $sortOrder => $itemId) {
            $data[] = [
                'id' => $itemId,
                'column_id' => $columnId,
                'sort_order' => $sortOrder,
            ];
        }

        $result = $this->linkModel->updateItem($data);

        if ($result->isSuccess()) {
            $this->invalidateItemsCache($columnId);
        }

        return $result;
    }

    public function toggleColumnActive(int $id, bool $isActive): ModelResult
    {
        $result = $this->columnModel->toggleActive($id, $isActive);

        if ($result->isSuccess()) {
            $this->clearCache();
        }

        return $result;
    }

    public function toggleItemActive(int $id, bool $isActive): ModelResult
    {
        $item = $this->linkModel->getItem($id);
        $result = $this->linkModel->toggleActive($id, $isActive);

        if ($result->isSuccess() && $item) {
            $this->invalidateItemsCache($item['column_id']);
        }

        return $result;
    }

    public function clearCache(): void
    {
        $this->cache->invalidateAllPages(self::CACHE_KEY);
        $this->cache->invalidateAllPages('footer_menu_column_*');
        $this->cache->invalidateAllPages('footer_menu_items_*');
    }

    public function getColumnCount(): int
    {
        return $this->columnModel->count(['is_active' => true]) ?? 0;
    }

    public function getItemCount(int $columnId): int
    {
        return $this->linkModel->getActiveItemCount($columnId);
    }

    private function invalidateColumnCache(int $columnId): void
    {
        $cacheKey = $this->getColumnCacheKey($columnId);
        $this->cache->invalidateAllPages($cacheKey);
        $this->invalidateStructureCache();
    }

    private function invalidateItemsCache(int $columnId): void
    {
        $cacheKey = $this->getItemsCacheKey($columnId);
        $this->cache->invalidateAllPages($cacheKey);
        $this->invalidateStructureCache();
    }

    private function invalidateStructureCache(): void
    {
        $this->cache->invalidateAllPages(self::CACHE_KEY);
    }

    private function getColumnCacheKey(int $columnId): string
    {
        return "footer_menu_column_{$columnId}";
    }

    private function getItemsCacheKey(int $columnId): string
    {
        return "footer_menu_items_{$columnId}";
    }

    private function loadFromDatabase(): array
    {
        $columns = $this->columnModel->getAllActiveColumns();

        if (empty($columns)) {
            return $this->getDefaultMenu();
        }

        $menu = [
            'columns' => [],
        ];

        foreach ($columns as $column) {
            $items = $this->linkModel->getColumnLinks($column['id']);

            $menu['columns'][$column['column_key']] = [
                'id' => $column['id'],
                'title' => $column['title'],
                'column_key' => $column['column_key'],
                'sort_order' => $column['sort_order'],
                'is_active' => $column['is_active'],
                'items' => array_map(fn ($item) => [
                    'id' => $item['id'],
                    'title' => $item['title'],
                    'url' => $item['url'],
                    'target' => $item['target'] ?? '_self',
                    'is_active' => $item['is_active'] ?? true,
                ], $items),
            ];
        }

        // Sort columns by sort_order
        usort($menu['columns'], fn ($a, $b) => $a['sort_order'] <=> $b['sort_order']);

        // Re-index by column_key after sorting
        $sortedColumns = [];
        foreach ($menu['columns'] as $column) {
            $sortedColumns[$column['column_key']] = $column;
        }
        $menu['columns'] = $sortedColumns;

        return $menu;
    }

    private function getDefaultMenu(): array
    {
        return [
            'columns' => [
                'services' => [
                    'id' => null,
                    'title' => 'Services',
                    'column_key' => 'services',
                    'sort_order' => 1,
                    'is_active' => true,
                    'items' => [
                        ['title' => 'Bonus program', 'url' => '/bonus', 'target' => '_self', 'is_active' => true],
                        ['title' => 'Gift cards', 'url' => '/gift-cards', 'target' => '_self', 'is_active' => true],
                        ['title' => 'Credit and payment', 'url' => '/payment', 'target' => '_self', 'is_active' => true],
                        ['title' => 'Service contracts', 'url' => '/contracts', 'target' => '_self', 'is_active' => true],
                        ['title' => 'Non-cash account', 'url' => '/non-cash', 'target' => '_self', 'is_active' => true],
                        ['title' => 'Payment', 'url' => '/payment', 'target' => '_self', 'is_active' => true],
                    ],
                ],
                'assistance' => [
                    'id' => null,
                    'title' => 'Assistance to the buyer',
                    'column_key' => 'assistance',
                    'sort_order' => 2,
                    'is_active' => true,
                    'items' => [
                        ['title' => 'Find an order', 'url' => '/order-status', 'target' => '_self', 'is_active' => true],
                        ['title' => 'Terms of delivery', 'url' => '/delivery', 'target' => '_self', 'is_active' => true],
                        ['title' => 'Exchange and return of goods', 'url' => '/returns', 'target' => '_self', 'is_active' => true],
                        ['title' => 'Guarantee', 'url' => '/guarantee', 'target' => '_self', 'is_active' => true],
                        ['title' => 'Frequently asked questions', 'url' => '/faq', 'target' => '_self', 'is_active' => true],
                        ['title' => 'Terms of use of the site', 'url' => '/terms', 'target' => '_self', 'is_active' => true],
                    ],
                ],
            ],
        ];
    }
}