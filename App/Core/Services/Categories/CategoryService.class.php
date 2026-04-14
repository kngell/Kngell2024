<?php

declare(strict_types=1);

/**
 * Single service that handles ALL category operations.
 */
class CategoryService
{
    public function __construct(
        private CategoryModel $model,
    ) {
    }

    /**
     * Get categories formatted for navigation.
     */
    public function getNavCategories(bool $onlyWithIcons = true, ?int $parentId = null): array
    {
        $conditions = ['is_active' => true];

        if ($onlyWithIcons) {
            $conditions['icon IS NOT NULL'] = true;
        }

        if ($parentId !== null) {
            $conditions['parent_id'] = $parentId;
        }

        $conditions['ORDER BY'] = 'order_index ASC, name ASC';

        $categories = $this->fetchCategories($conditions);

        return array_map(fn ($cat) => $this->formatNavItem($cat), $categories);
    }

    /**
     * Get root categories (level 0).
     */
    public function getRootCategories(bool $onlyWithIcons = true): array
    {
        $conditions = [
            'is_active' => true,
            'level' => 0,
            'ORDER BY' => 'order_index ASC, name ASC',
        ];

        if ($onlyWithIcons) {
            $conditions['icon IS NOT NULL'] = true;
        }

        $categories = $this->fetchCategories($conditions);

        return array_map(fn ($cat) => $this->formatNavItem($cat), $categories);
    }

    /**
     * Get complete mega menu structure (nested).
     */
    public function getMegaMenu(): array
    {
        $tree = $this->getActiveNestedTree();

        return array_map(fn ($cat) => $this->formatMegaMenuItem($cat), $tree);
    }

    /**
     * Get options for select dropdown.
     */
    public function getSelectOptions(string $emptyLabel = '-- Select a Category --'): array
    {
        try {
            $tree = $this->getActiveNestedTree();
            $options = ['' => $emptyLabel];

            foreach ($tree as $category) {
                $options = array_merge($options, $this->buildIndentedOptions($category));
            }

            return $options;
        } catch (Throwable $e) {
            error_log('Failed to load category options: ' . $e->getMessage());
            return ['' => $emptyLabel];
        }
    }

    /**
     * Get child categories of a specific parent.
     */
    public function getChildCategories(int $parentId, bool $onlyWithIcons = false): array
    {
        $conditions = [
            'parent_id' => $parentId,
            'is_active' => true,
            'ORDER BY' => 'order_index ASC, name ASC',
        ];

        if ($onlyWithIcons) {
            $conditions['icon IS NOT NULL'] = true;
        }

        $categories = $this->fetchCategories($conditions);

        return array_map(fn ($cat) => $this->formatNavItem($cat), $categories);
    }

    /**
     * Get flat array of all active categories.
     */
    public function getAllActive(): array
    {
        return $this->fetchCategories(['is_active' => true]);
    }

    /**
     * Get nested tree structure.
     */
    public function getNestedTree(): array
    {
        return $this->getActiveNestedTree();
    }

    // ==================== PRIVATE METHODS ====================

    private function fetchCategories(array $conditions = []): array
    {
        $result = $this->model->all($conditions);

        if (!$result->isSuccess()) {
            return [];
        }

        return $result->asClass();
    }

    private function getActiveCategories(): array
    {
        return $this->fetchCategories(['is_active' => true]);
    }

    private function getActiveNestedTree(): array
    {
        $flatCategories = $this->getActiveCategories();
        return $this->buildNestedTree($flatCategories);
    }

    private function buildNestedTree(array $flatCategories): array
    {
        $tree = [];
        $map = [];

        // Create map
        foreach ($flatCategories as $category) {
            $category->initChildren();
            $map[$category->getId()] = $category;
        }

        // Build tree
        foreach ($map as $category) {
            $parentId = $category->getParentId();

            if ($parentId === null || $parentId === 0) {
                $tree[] = $category;
            } elseif (isset($map[$parentId])) {
                $map[$parentId]->addChildren($category);
            }
        }

        return $tree;
    }

    private function formatNavItem($category): array
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'icon' => $category->getIcon(),
            'slug' => $category->getSlug(),
            'level' => $category->getLevel(),
            'parent_id' => $category->getParentId(),
        ];
    }

    private function formatMegaMenuItem($category): array
    {
        return [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'icon' => $category->getIcon(),
            'slug' => $category->getSlug(),
            'children' => array_map(
                fn ($child) => $this->formatMegaMenuItem($child),
                $category->getChildren()->all(),
            ),
        ];
    }

    private function buildIndentedOptions($category, string $prefix = ''): array
    {
        $options = [];
        $indent = str_repeat('-', strlen($prefix) * 2) . ' ';

        $label = $prefix . $category->getName();
        $options[$category->getId()] = $label;

        if ($category->isInitialized('children') && !$category->getChildren()->isEmpty()) {
            foreach ($category->getChildren()->all() as $child) {
                $options = array_merge(
                    $options,
                    $this->buildIndentedOptions($child, $prefix . $indent),
                );
            }
        }

        return $options;
    }
}