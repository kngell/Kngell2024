<?php

declare(strict_types=1);

class CleanupFooterAboutPaginatedCacheListener extends AbstractCleanupPaginatedCacheListener
{
    public function __construct(
        PaginatedCacheFactory $cacheFactory,
        private readonly FooterAboutModel $model,
    ) {
        parent::__construct($cacheFactory);
    }

    protected function getPaginatedAdapter(): PaginatedEntityAdapterInterface
    {
        return new FooterAboutPaginatedAdapter(
            $this->model,
        );
    }

    protected function getCacheFolder(): string
    {
        $entityKey = EntityKey::fromEntityClass(FooterAbout::class);
        if ($entityKey === null) {
            throw new InvalidArgumentException(sprintf('Unknown entity class: %s', FooterAbout::class));
        }
        return $entityKey->getKey();
    }

    protected function getEntityKey(): EntityKey
    {
        return EntityKey::FOOTER_ABOUT;
    }
}