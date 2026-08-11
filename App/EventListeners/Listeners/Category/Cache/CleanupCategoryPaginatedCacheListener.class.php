<?php

declare(strict_types=1);

class CleanupCategoryPaginatedCacheListener extends AbstractCleanupPaginatedCacheListener
{
    public function __construct(
        PaginatedCacheFactory $cacheFactory,
        private readonly CategoryModel $model,
    ) {
        parent::__construct($cacheFactory);
    }

    protected function getCacheFolder(): string
    {
        return EntityKey::CATEGORY->value;
    }

    protected function getPaginatedAdapter(): PaginatedEntityAdapterInterface
    {
        return new CategoryPaginatedAdapter($this->model);
    }

    protected function getEntityKey(): EntityKey
    {
        return EntityKey::CATEGORY;
    }
}