<?php

declare(strict_types=1);

class CleanupFooterMenuSectionCacheListener extends AbstractSectionCacheCleanupListener
{
    public function __construct(
        ImageCacheFactory $imageCacheFactory,
        private FooterMenuCacheManagerFactory $cacheFactory,
    ) {
        parent::__construct($imageCacheFactory);
    }

    /**
     * @param FooterMenuShow $entity
     *
     * @throws Exception
     *
     * @return null|string|array
     */
    protected function getOldImageUrl(Entity $entity): null|string|array
    {
        return null;
    }

    #[Override]
    protected function getNewImageUrl(array $payload): null|string|array
    {
        return null;
    }

    #[Override]
    protected function getCacheManager(): HtmlSectionCacheManager
    {
        return $this->cacheFactory->create();
    }

    #[Override]
    protected function getServiceClass(): string
    {
        return FooterService::class;
    }

    #[Override]
    protected function getEntityType(): string
    {
        return FooterMenuShow::class;
    }
}