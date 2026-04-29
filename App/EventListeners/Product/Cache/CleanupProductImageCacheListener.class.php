<?php

declare(strict_types=1);

class CleanupProductImageCacheListener extends AbstractImageCacheCleanupListener
{
    public function __construct(
        ImageCacheFactory $imageCacheFactory,
        private ProductCacheManagerFactory $productCacheFactory,
    ) {
        parent::__construct($imageCacheFactory);
    }

    protected function getEntityId(array $payload): ?int
    {
        return $payload['id']['value'] ?? null;
    }

    protected function getOldEntity(array $payload): ?object
    {
        return $payload['model_data']['old_hero_snapshot'] ?? null;
    }

    protected function getOldImageUrl(object $entity): ?string
    {
        if (!$entity instanceof Product) {
            return null;
        }
        return $entity->getMainImage();
    }

    protected function getNewImageUrl(array $payload): ?string
    {
        return $payload['media']['main_image'] ?? null;
    }

    protected function getPageTarget(array $payload): string
    {
        return $payload['form_data']['page_target'] ?? 'index';
    }

    protected function getCacheManager(): HtmlSectionCacheManager
    {
        return $this->productCacheFactory->create();
    }

    protected function getServiceClass(): string
    {
        return ProductService::class;
    }

    protected function getEntityType(): string
    {
        return 'ProductShow';
    }
}