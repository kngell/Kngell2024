<?php

declare(strict_types=1);

class CleanupProductPaginatedCacheListener extends AbstractCleanupPaginatedCacheListener
{
    public function __construct(
        PaginatedCacheFactory $cacheFactory,
        private readonly ProductShowModel $model,
    ) {
        parent::__construct($cacheFactory);
    }

    protected function getCacheFolder(): string
    {
        return EntityKey::PRODUCT->value;
    }

    protected function getPaginatedAdapter(): PaginatedEntityAdapterInterface
    {
        return new ProductPaginatedAdapter($this->model);
    }

    protected function getEntityKey(): EntityKey
    {
        return EntityKey::PRODUCT;
    }
}