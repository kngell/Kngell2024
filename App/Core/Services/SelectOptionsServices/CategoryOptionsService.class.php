<?php

declare(strict_types=1);

class CategoryOptionsService implements SelectOptionsServiceInterface
{
    private const string SELECT_LABLE = '-- Select a Category --';

    public function __construct(
        private CategoryModel $md,
    ) {
    }

    public function getActiveOptions(): array
    {
        try {
            $flatCategories = $this->md->all(['is_active', true])->asClass();

            $nestedTree = $this->buildNestedTree($flatCategories);

            $options = ['' => self::SELECT_LABLE];
            return array_merge($options, $this->buildIndentedOptions($nestedTree));
        } catch (Throwable $e) {
            throw new LogicException('CategoryOptionsService: Failed to load Categories - ' . $e->getMessage());
            // return $this->getDefaultOption();
        }
    }

    private function buildNestedTree(array $flatCategories): array
    {
        $tree = [];
        $map = [];

        foreach ($flatCategories as $category) {
            $category->initChildren();
            $map[$category->getId()] = $category;
        }

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

    private function buildIndentedOptions(array $categories, string $prefix = ''): array
    {
        $options = [];
        $indent = str_repeat('-', strlen($prefix) * 2) . ' ';

        /** @var Category $category */
        foreach ($categories as $category) {
            $label = $prefix . $category->getName();
            $options[$category->getId()] = $label;
            if ($category->isInitialized('children') && !$category->getChildren()->isEmpty()) {
                $options = array_merge(
                    $options,
                    $this->buildIndentedOptions($category->getChildren()->all(), $prefix . $indent),
                );
            }
        }
        return $options;
    }
}