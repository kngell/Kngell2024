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

    protected function getOldImageUrl(Entity $entity): null|string|array
    {
        if (!$entity instanceof Product) {
            return null;
        }
        return $entity->getMainImage();
    }

    protected function getNewImageUrl(array $payload): ?string
    {
        return $payload['form_data']['main_image'] ?? null;
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
        return ProductShow::class;
    }
}