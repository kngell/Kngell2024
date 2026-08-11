<?php

declare(strict_types=1);
class CategoryService
{
    private const string SELECT_LABEL = '-- Select a Category --';

    public function __construct(
        private CategoryModel $model,
    ) {
    }

    public function getNavCategories(bool $onlyWithIcons = true, ?int $parentId = null): array
    {
        $conditions = ['is_active' => true];

        if ($onlyWithIcons) {
            $conditions = array_merge($conditions, ['icon IS NOT NULL']);
        }

        if ($parentId !== null) {
            $conditions = array_merge($conditions, ['parent_id' => $parentId]);
        }

        $conditions['ORDER BY'] = 'order_index ASC, name ASC';
        $conditions['limit'] = 50;

        $categories = $this->fetchCategories($conditions);

        return array_map(fn ($cat) => $this->formatNavItem($cat), $categories);
    }

    public function getRootCategories(bool $onlyWithIcons = true): array
    {
        $conditions = [
            'is_active' => true,
            'level' => 0,
            'ORDER BY' => 'order_index ASC, name ASC',
        ];

        if ($onlyWithIcons) {
            $conditions = array_merge($conditions, ['icon IS NOT NULL']);
        }

        $categories = $this->fetchCategories($conditions);

        return array_map(fn ($cat) => $this->formatNavItem($cat), $categories);
    }

    public function getActiveOptions(): array
    {
        try {
            $nestedTree = $this->getActiveNestedTree();

            $options = ['' => self::SELECT_LABEL];
            return array_merge($options, $this->buildIndentedOptions($nestedTree));
        } catch (Throwable $e) {
            error_log('CategoryOptionsService: Failed to load Categories - ' . $e->getMessage());
            return $this->getDefaultOption();
        }
    }

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

    private function getDefaultOption(): array
    {
        return ['' => self::SELECT_LABEL];
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

        $category = is_array($category) ? reset($category) : $category;

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