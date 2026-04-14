<?php

declare(strict_types=1);

class CleanupSmallBannerImageListener extends AbstractImageCacheCleanupListener
{
    public function __construct(
        ImageCacheFactory $imageCacheFactory,
        private SmallBannerCacheManagerFactory $bannerCacheFactory,
    ) {
        parent::__construct($imageCacheFactory);
    }

    protected function getEntityId(array $payload): ?int
    {
        return $payload['small_banner_id'] ?? null;
    }

    protected function getOldEntity(array $payload): ?object
    {
        return $payload['model_data']['old_entity_snapshot'] ?? null;
    }

    protected function getOldImageUrl(object $entity): ?string
    {
        if (!$entity instanceof SmallBanner) {
            return null;
        }
        return $entity->getCustomImageUrl();
    }

    protected function getNewImageUrl(array $payload): ?string
    {
        return $payload['media']['custom_image_url'] ?? null;
    }

    protected function getPageTarget(array $payload): string
    {
        return $payload['form_data']['page_target'] ?? 'index';
    }

    protected function getCacheManager()
    {
        return $this->bannerCacheFactory->create();
    }

    protected function getServiceClass(): string
    {
        return SmallBannerService::class;
    }

    protected function getEntityType(): string
    {
        return 'Small Banner';
    }
}