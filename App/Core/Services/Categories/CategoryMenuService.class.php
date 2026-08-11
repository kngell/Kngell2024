<?php

declare(strict_types=1);

class CategoryMenuService
{
    private HtmlSectionCacheManager $cache;

    public function __construct(
        private CategoryModel $model,
        private CategoryMenuTransformer $transformer,
        CategoryCacheManagerFactory $cacheFactory,
    ) {
        $this->cache = $cacheFactory->create();
    }

    // ==================== 1. MEGA MENU (FIXED KEY STRUCTURE) ====================

    public function getMegaMenu(bool $onlyWithIcons = false, ?int $maxDepth = null): array
    {
        $pageSuffix = 'transformed_mega_menu'
            . ($onlyWithIcons ? '_with_icons' : '')
            . ($maxDepth !== null ? "_depth_{$maxDepth}" : '');

        // FIX: Route through getPageCacheKey to maintain signature compatibility with listeners
        $pageKey = $this->cache->getPageCacheKey($pageSuffix, static::class);

        return $this->cache->remember($pageKey, function () use ($onlyWithIcons, $maxDepth) {
            $allCategories = $this->fetchAllCategoriesFromDb();
            if (empty($allCategories)) {
                return [];
            }

            $entities = array_filter($allCategories, function (Category $category) use ($onlyWithIcons) {
                return !($onlyWithIcons && $category->getIcon() === null);
            });

            $tree = $this->transformer->buildTree(array_values($entities));
            if ($maxDepth !== null) {
                $tree = $this->transformer->limitTreeDepth($tree, $maxDepth);
            }

            return $this->transformer->transformForMenu($tree, true);
        }, 3600);
    }

    // ==================== 2. FLAT MENU (ALREADY GOOD) ====================

    public function getFlatMenu(bool $onlyWithIcons = false, ?int $parentId = null): array
    {
        $pageKey = 'flat_menu'
            . ($onlyWithIcons ? '_with_icons' : '')
            . ($parentId !== null ? "_parent_{$parentId}" : '');

        $entities = $this->cache->getEntitiesForPage(
            $pageKey,
            static::class,
            fn () => $this->fetchMenuEntitiesFromDb($onlyWithIcons, $parentId),
            fn (array $ids) => $this->fetchEntitiesDbByIds($ids),
        );

        if (empty($entities)) {
            return [];
        }

        $allCategories = $this->cache->getEntitiesForPage(
            'global_menu_context_all',
            static::class,
            fn () => $this->fetchMenuEntitiesFromDb(false, null),
            fn (array $ids) => $this->fetchEntitiesDbByIds($ids),
        );

        return $this->transformer->addHierarchyInfo($entities, $allCategories);
    }

    // ==================== 3. BREADCRUMBS (FULLY CACHE SHIELDED) ====================

    public function getBreadcrumbs(int $categoryId): array
    {
        $pageSuffix = "breadcrumbs_{$categoryId}";
        $pageKey = $this->cache->getPageCacheKey($pageSuffix, static::class);

        return $this->cache->remember($pageKey, function () use ($categoryId) {
            // FIX: Shield DB by retrieving via Level-2 entity structure
            $category = $this->getSingleCategoryCached($categoryId);
            if (!$category) {
                return [];
            }

            // FIX: Pull full structural context cleanly from Level-2 page caching
            $allCategories = $this->getGlobalCategoriesContextCached();

            return $this->transformer->buildBreadcrumbs($category, $allCategories);
        }, 3600);
    }

    // ==================== 4. BREADCRUMB SCHEMA (FULLY CACHE SHIELDED) ====================

    public function getBreadcrumbSchema(int $categoryId): array
    {
        $pageSuffix = "breadcrumb_schema_{$categoryId}";
        $pageKey = $this->cache->getPageCacheKey($pageSuffix, static::class);

        return $this->cache->remember($pageKey, function () use ($categoryId) {
            // FIX: Shield DB by retrieving via Level-2 entity structure
            $category = $this->getSingleCategoryCached($categoryId);
            if (!$category) {
                return [];
            }

            // FIX: Pull full structural context cleanly from Level-2 page caching
            $allCategories = $this->getGlobalCategoriesContextCached();

            return $this->transformer->buildBreadcrumbSchema($category, $allCategories);
        }, 3600);
    }

    // ==================== INTERNAL LEVEL-2 WRAPPERS ====================

    /**
     * Internal abstraction to ensure single category lookups remain in level-2 cache.
     */
    private function getSingleCategoryCached(int $categoryId): ?Category
    {
        return $this->cache->getEntityForPage(
            "lookup_cat_id_{$categoryId}",
            static::class,
            fn () => $this->fetchCategoryById($categoryId),
            fn ($id) => $this->fetchCategoryById((int) $id),
        );
    }

    /**
     * Shared cache pipeline to fetch unified, full structural tree environments safely.
     */
    private function getGlobalCategoriesContextCached(): array
    {
        return $this->cache->getEntitiesForPage(
            'global_menu_context_all',
            static::class,
            fn () => $this->fetchMenuEntitiesFromDb(false, null),
            fn (array $ids) => $this->fetchEntitiesDbByIds($ids),
        );
    }

    // ==================== DATABASE LOADERS ====================

    private function fetchAllCategoriesFromDb(): array
    {
        $result = $this->model->all([
            'is_active' => true,
            'deleted_at is null',
            'ORDER BY' => ['order_index ASC', 'name ASC'],
        ]);

        return $result->isSuccess() ? $result->asClass() : [];
    }

    private function fetchMenuEntitiesFromDb(bool $onlyWithIcons, ?int $parentId = null): array
    {
        $conditions = [
            'is_active' => true,
            'deleted_at is null',
            'ORDER BY' => ['order_index ASC', 'name ASC'],
        ];

        if ($onlyWithIcons) {
            $conditions[] = 'icon IS NOT NULL';
        }

        if ($parentId !== null) {
            $conditions['parent_id'] = $parentId;
        }

        $result = $this->model->all($conditions);
        return $result->isSuccess() ? $result->asClass() : [];
    }

    private function fetchEntitiesDbByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $result = $this->model->all([
            'id' => [$ids],
            'is_active' => true,
            'deleted_at is null',
        ]);

        return $result->isSuccess() ? $result->asClass() : [];
    }

    private function fetchCategoryById(int $id): ?Category
    {
        $result = $this->model->first(['id' => $id]);
        return $result->isSuccess() ? $result->asClass() : null;
    }
}