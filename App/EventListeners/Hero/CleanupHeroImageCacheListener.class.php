<?php

declare(strict_types=1);

class CleanupHeroImageCacheListener extends AbstractImageCacheCleanupListener
{
    public function __construct(
        ImageCacheFactory $imageCacheFactory,
        private HeroCacheManagerFactory $heroCacheFactory,
    ) {
        parent::__construct($imageCacheFactory);
    }

    protected function getEntityId(array $payload): ?int
    {
        return $payload['hero_id'] ?? null;
    }

    protected function getOldEntity(array $payload): ?object
    {
        return $payload['model_data']['old_hero_snapshot'] ?? null;
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
        return $payload['media']['image_url'] ?? null;
    }

    protected function getPageTarget(array $payload): string
    {
        return $payload['form_data']['page_target'] ?? 'index';
    }

    protected function getCacheManager()
    {
        return $this->heroCacheFactory->create();
    }

    protected function getServiceClass(): string
    {
        return HeroService::class;
    }

    protected function getEntityType(): string
    {
        return 'Hero';
    }
}