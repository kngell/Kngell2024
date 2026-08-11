<?php

declare(strict_types=1);

class CleanupCategoryImageCacheListener extends AbstractSectionCacheCleanupListener
{
    public function __construct(
        ImageCacheFactory $imageCacheFactory,
        private CategoryCacheManagerFactory $categoryCacheFactory,
    ) {
        parent::__construct($imageCacheFactory);
    }

    protected function getOldImageUrl(Entity $entity): ?string
    {
        if (!$entity instanceof Category) {
            return null;
        }
        return $entity->getImageUrl();
    }

    protected function getNewImageUrl(array $payload): ?string
    {
        return $payload['form_data']['image_url'] ?? null;
    }

    protected function getCacheManager(): HtmlSectionCacheManager
    {
        return $this->categoryCacheFactory->create();
    }

    protected function getServiceClass(): string
    {
        return CategoryMenuService::class;
    }

    protected function getEntityType(): string
    {
        return Category::class;
    }
}