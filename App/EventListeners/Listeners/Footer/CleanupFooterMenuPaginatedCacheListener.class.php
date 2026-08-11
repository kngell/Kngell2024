<?php

declare(strict_types=1);

class CleanupFooterMenuPaginatedCacheListener extends AbstractCleanupPaginatedCacheListener
{
    public function __construct(
        PaginatedCacheFactory $cacheFactory,
        private readonly FooterMenuShowModel $model,
    ) {
        parent::__construct($cacheFactory);
    }

    protected function getPaginatedAdapter(): PaginatedEntityAdapterInterface
    {
        return new FooterColumnsPaginatedAdapter(
            $this->model,
        );
    }

    protected function getCacheFolder(): string
    {
        $entityKey = EntityKey::fromEntityClass(FooterMenuShow::class);
        if ($entityKey === null) {
            throw new InvalidArgumentException(sprintf('Unknown entity class: %s', FooterMenuShow::class));
        }
        return $entityKey->getKey();
    }

    protected function getEntityKey(): EntityKey
    {
        return EntityKey::FOOTER_COLUMN;
    }
}