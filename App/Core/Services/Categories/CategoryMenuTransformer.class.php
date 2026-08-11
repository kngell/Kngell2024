<?php

declare(strict_types=1);

class CategoryMenuTransformer
{
    public function buildTree(array $flatCategories): array
    {
        $map = [];
        $tree = [];
        /** @var Category $category */
        foreach ($flatCategories as $category) {
            $category->setChildren(new Collection());
            $map[$category->getId()] = $category;
        }

        foreach ($map as $category) {
            $parentId = $category->getParentId();
            if ($parentId && isset($map[$parentId])) {
                $map[$parentId]->addChildren($category);
            } else {
                $tree[] = $category;
            }
        }

        return $tree;
    }

    public function transformForMenu(array $tree, bool $recursive = true): array
    {
        $result = [];

        foreach ($tree as $category) {
            $item = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'icon' => $category->getIcon(),
                'url' => "/category/{$category->getSlug()}",
                'has_children' => !empty($category->children),
            ];

            if ($recursive && !empty($category->children)) {
                $item['children'] = $this->transformForMenu($category->children, true);
            }

            $result[] = $item;
        }

        return $result;
    }

    public function addHierarchyInfo(array $categories, array $allCategories): array
    {
        $tree = $this->buildTree($allCategories);
        $levelMap = $this->buildLevelMap($tree);

        foreach ($categories as $category) {
            $category->level = $levelMap[$category->getId()] ?? 0;
            $category->indented_name = $this->getIndentedName($category);
            $category->parent_name = $this->getParentName($category, $allCategories);
        }

        return $categories;
    }

    public function limitTreeDepth(array $tree, int $maxDepth, int $currentDepth = 0): array
    {
        if ($currentDepth >= $maxDepth) {
            return [];
        }

        foreach ($tree as $category) {
            if (!empty($category->children)) {
                $category->children = $this->limitTreeDepth(
                    $category->children,
                    $maxDepth,
                    $currentDepth + 1,
                );
            }
        }

        return $tree;
    }

    public function buildBreadcrumbs(Category $category, array $allCategories): array
    {
        $breadcrumbs = [];
        $currentId = $category->getId();

        $path = [];
        while ($currentId) {
            $current = $this->findCategoryById($allCategories, $currentId);
            if (!$current) {
                break;
            }

            array_unshift($path, $current);
            $currentId = $current->getParentId();
        }

        foreach ($path as $index => $cat) {
            $breadcrumbs[] = [
                'id' => $cat->getId(),
                'name' => $cat->getName(),
                'slug' => $cat->getSlug(),
                'url' => "/category/{$cat->getSlug()}",
                'is_active' => ($index === count($path) - 1),
            ];
        }

        return $breadcrumbs;
    }

    public function buildBreadcrumbSchema(Category $category, array $allCategories): array
    {
        $breadcrumbs = $this->buildBreadcrumbs($category, $allCategories);

        $items = [];
        $position = 1;

        $items[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Home',
            'item' => '/',
        ];

        foreach ($breadcrumbs as $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    private function buildLevelMap(array $tree, int $currentLevel = 0, array &$map = []): array
    {
        foreach ($tree as $category) {
            $map[$category->getId()] = $currentLevel;
            if (!empty($category->children)) {
                $this->buildLevelMap($category->children, $currentLevel + 1, $map);
            }
        }

        return $map;
    }

    private function getIndentedName(Category $category): string
    {
        $level = $category->getLevel() ?? 0;
        $indent = $level > 0 ? str_repeat('—', $level) . ' ' : '';
        return $indent . $category->getName();
    }

    private function getParentName(Category $category, array $allCategories): string
    {
        $parentId = $category->getParentId();
        if (!$parentId) {
            return '— Root —';
        }

        foreach ($allCategories as $parent) {
            if ($parent->getId() === $parentId) {
                return $parent->getName();
            }
        }

        return '—';
    }

    private function findCategoryById(array $categories, int $id): ?Category
    {
        foreach ($categories as $category) {
            if ($category->getId() === $id) {
                return $category;
            }
        }

        return null;
    }
}