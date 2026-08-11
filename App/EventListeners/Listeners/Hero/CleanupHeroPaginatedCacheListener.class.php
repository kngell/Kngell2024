<?php

declare(strict_types=1);

class CleanupHeroPaginatedCacheListener extends AbstractCleanupPaginatedCacheListener
{
    public function __construct(
        PaginatedCacheFactory $cacheFactory,
        private readonly HeroModel $model,
    ) {
        parent::__construct($cacheFactory);
    }

    protected function getPaginatedAdapter(): PaginatedEntityAdapterInterface
    {
        return new HeroPaginatedAdapter($this->model);
    }

    protected function getEntityKey(): EntityKey
    {
        return EntityKey::HERO;
    }
}