<?php

declare(strict_types=1);

class CleanupSmallBannerImageCacheListener extends AbstractImageCacheCleanupListener
{
    public function __construct(
        ImageCacheFactory $imageCacheFactory,
        private SmallBannerCacheManagerFactory $CacheFactory,
    ) {
        parent::__construct($imageCacheFactory);
    }

    protected function getEntityId(array $payload): ?int
    {
        return $payload['data']['id'] ?? null;
    }

    protected function getOldImageUrl(object $entity): ?string
    {
        if (!$entity instanceof Hero) {
            return null;
        }
        return $entity->getImageUrl();
    }

    protected function getNewImageUrl(array $payload): ?string
    {
        return $payload['data']['image_url'] ?? null;
    }

    protected function getPageTarget(array $payload): string
    {
        return $payload['form_data']['page_target'] ?? 'index';
    }

    protected function getCacheManager(): HtmlSectionCacheManager
    {
        return $this->CacheFactory->create();
    }

    protected function getServiceClass(): string
    {
        return SmallBannerService::class;
    }

    protected function getEntityType(): string
    {
        return SmallBannerShow::class;
    }
}