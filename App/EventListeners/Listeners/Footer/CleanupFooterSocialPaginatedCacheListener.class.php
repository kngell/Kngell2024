<?php

declare(strict_types=1);

class CleanupFooterSocialPaginatedCacheListener extends AbstractCleanupPaginatedCacheListener
{
    public function __construct(
        PaginatedCacheFactory $cacheFactory,
        private readonly FooterSocialModel $model,
    ) {
        parent::__construct($cacheFactory);
    }

    protected function getPaginatedAdapter(): PaginatedEntityAdapterInterface
    {
        return new FooterSocialPaginatedAdapter(
            $this->model,
        );
    }

    protected function getCacheFolder(): string
    {
        $entityKey = EntityKey::fromEntityClass(FooterSocial::class);
        if ($entityKey === null) {
            throw new InvalidArgumentException(sprintf('Unknown entity class: %s', FooterSocial::class));
        }
        return $entityKey->getKey();
    }

    protected function getEntityKey(): EntityKey
    {
        return EntityKey::FOOTER_SOCIAL;
    }
}