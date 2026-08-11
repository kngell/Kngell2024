<?php

declare(strict_types=1);

class CleanupSmallBannerPaginatedCacheListener extends AbstractCleanupPaginatedCacheListener
{
    public function __construct(
        PaginatedCacheFactory $cacheFactory,
        private readonly SmallBannerShowModel $model,
    ) {
        parent::__construct($cacheFactory);
    }

    protected function getPaginatedAdapter(): PaginatedEntityAdapterInterface
    {
        return new SmallBannerPaginatedAdapter($this->model);
    }

    protected function getEntityKey(): EntityKey
    {
        return EntityKey::SMALL_BANNER;
    }
}