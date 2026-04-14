<?php

declare(strict_types=1);

abstract class AbstractImageCacheCleanupListener implements EventListenerInterface
{
    public function __construct(
        protected ImageCacheFactory $imageCacheFactory,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $operation = $payload['operation'] ?? null;

        // Handle different operations
        switch ($operation) {
            case 'insert':
                $this->handleInsert($payload);
                break;
            case 'update':
                $this->handleUpdate($payload);
                break;
            case 'delete':
                $this->handleDelete($payload);
                break;
            default:
                // Fallback to update logic for backward compatibility
                $this->handleUpdate($payload);
        }

        return null;
    }

    protected function handleInsert(array $payload): void
    {
        $entityId = $this->getEntityId($payload);
        $pageTarget = $this->getPageTarget($payload);

        if (!$entityId) {
            return;
        }

        // On insert, invalidate page cache to show the new entity
        $this->cleanupPageCache($pageTarget);

        $this->logInsertCleanup($entityId, $pageTarget);
    }

    protected function handleUpdate(array $payload): void
    {
        $entityId = $this->getEntityId($payload);
        $pageTarget = $this->getPageTarget($payload);
        $oldEntity = $this->getOldEntity($payload);
        $newImageUrl = $this->getNewImageUrl($payload);

        if (!$oldEntity || !$entityId) {
            return;
        }

        $oldImageUrl = $this->getOldImageUrl($oldEntity);

        // Clean up image cache if image changed
        if ($oldImageUrl !== null && $oldImageUrl !== $newImageUrl) {
            $this->cleanupImageCache($oldImageUrl, $entityId, $pageTarget);
        }

        // Clean up entity and page cache
        $this->cleanupEntityAndPageCache($oldEntity, $pageTarget);
    }

    protected function handleDelete(array $payload): void
    {
        $oldEntity = $this->getOldEntity($payload);
        $pageTarget = $this->getPageTarget($payload);

        if (!$oldEntity) {
            return;
        }

        // Get entity ID directly from the entity
        $entityId = (int) $oldEntity->getEntityPrimarykeyValue();
        $oldImageUrl = $this->getOldImageUrl($oldEntity);

        // Clean up image cache for the deleted entity's images
        if ($oldImageUrl !== null) {
            $this->cleanupImageCache($oldImageUrl, $entityId, $pageTarget);
        }

        // Clean up entity and page cache
        $this->cleanupEntityAndPageCache($oldEntity, $pageTarget);

        $this->logDeleteCleanup($entityId, $pageTarget);
    }

    protected function cleanupEntityAndPageCache(object $entity, string $pageTarget): void
    {
        $cacheManager = $this->getCacheManager();
        $serviceClass = $this->getServiceClass();

        // Invalidate specific entity
        $cacheManager->invalidateEntity($entity, $serviceClass);

        // Invalidate the page where this entity appears
        $cacheManager->invalidatePage($pageTarget, $serviceClass);

        // Clear the entire section cache for this entity type
        $cacheManager->clearSection($serviceClass);
    }

    protected function cleanupPageCache(string $pageTarget): void
    {
        $cacheManager = $this->getCacheManager();
        $serviceClass = $this->getServiceClass();

        // Just invalidate the page without entity-specific cleanup
        $cacheManager->invalidatePage($pageTarget, $serviceClass);
        $cacheManager->clearSection($serviceClass);
    }

    protected function cleanupImageCache(string $oldImageUrl, int $entityId, string $pageTarget): void
    {
        $imageCache = $this->imageCacheFactory->create('images');

        // This deletes all cached variants of the image
        $deletedCount = $imageCache->deleteImageCache($oldImageUrl);

        // Also cleanup orphaned tags to keep cache clean
        $imageCache->cleanupOrphanedTags();

        error_log(sprintf(
            'Deleted %d cached variants for image: %s',
            $deletedCount,
            basename($oldImageUrl),
        ));

        $this->logImageCleanup($entityId, basename($oldImageUrl), $pageTarget, $deletedCount);
    }

    protected function logInsertCleanup(int $entityId, string $pageTarget): void
    {
        error_log(sprintf(
            '%s created: ID %d, Page: %s, Cache cleaned: [page, section]',
            $this->getEntityType(),
            $entityId,
            $pageTarget,
        ));
    }

    protected function logDeleteCleanup(int $entityId, string $pageTarget): void
    {
        error_log(sprintf(
            '%s deleted: ID %d, Page: %s, Cache cleaned: [images, entity, page, section]',
            $this->getEntityType(),
            $entityId,
            $pageTarget,
        ));
    }

    protected function logImageCleanup(int $entityId, string $filename, string $pageTarget, int $deletedCount): void
    {
        error_log(sprintf(
            '%s cache cleaned: ID %d, Image: %s, Page: %s, Variants deleted: %d, Types: [images, entity, page, section]',
            $this->getEntityType(),
            $entityId,
            $filename,
            $pageTarget,
            $deletedCount,
        ));
    }

    // Abstract methods
    abstract protected function getEntityId(array $payload): ?int;

    abstract protected function getOldEntity(array $payload): ?object;

    abstract protected function getOldImageUrl(object $entity): ?string;

    abstract protected function getNewImageUrl(array $payload): ?string;

    abstract protected function getPageTarget(array $payload): string;

    abstract protected function getCacheManager();

    abstract protected function getServiceClass(): string;

    abstract protected function getEntityType(): string;
}