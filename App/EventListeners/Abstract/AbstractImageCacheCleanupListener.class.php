<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

abstract class AbstractImageCacheCleanupListener implements EventListenerInterface
{
    public function __construct(
        protected ImageCacheFactory $imageCacheFactory,
        protected ?LoggerInterface $logger = null,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $operation = $this->resolveOperation($payload);

        match ($operation) {
            'INSERT' => $this->handleInsert($payload),
            'UPDATE' => $this->handleUpdate($payload),
            'DELETE' => $this->handleDelete($payload),
        };

        return null;
    }

    protected function handleInsert(array $payload): void
    {
        $entityId = $this->getEntityId($payload);
        $pageTarget = $this->getPageTarget($payload);

        if (!$entityId) {
            return;
        }

        $cacheManager = $this->getCacheManager();
        $serviceClass = $this->getServiceClass();
        $cacheManager->invalidateAllPages($serviceClass);

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
        $oldEntity = $payload['record'];
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
        $this->cleanupAllCache($oldEntity);

        $this->logDeleteCleanup($entityId, $pageTarget);
    }

    protected function cleanupEntityAndPageCache(object $entity, string $pageTarget): void
    {
        $cacheManager = $this->getCacheManager();
        $serviceClass = $this->getServiceClass();

        $cacheManager->invalidateEntity($entity);
        $cacheManager->invalidatePage($pageTarget, $serviceClass);
    }

    protected function cleanupAllCache(object $entity): void
    {
        $cacheManager = $this->getCacheManager();
        $serviceClass = $this->getServiceClass();

        $cacheManager->invalidateEntity($entity);
        $cacheManager->invalidateAllPages($serviceClass);
    }

    protected function cleanupPageCache(string $pageTarget): void
    {
        $cacheManager = $this->getCacheManager();
        $serviceClass = $this->getServiceClass();

        // Just invalidate the page without entity-specific cleanup
        $cacheManager->invalidatePage($pageTarget, $serviceClass);
        $cacheManager->invalidateAllPages($serviceClass);
    }

    protected function cleanupImageCache(string $oldImageUrl, int $entityId, string $pageTarget): void
    {
        $imageCache = $this->imageCacheFactory->create('images');
        $deletedCount = $imageCache->deleteImageCache($oldImageUrl);
        // $imageCache->cleanupOrphanedTags(); // will go to Maintenance event

        $this->logger?->info('Image cache cleaned', [
            'entity_type' => $this->getEntityType(),
            'entity_id' => $entityId,
            'image' => basename($oldImageUrl),
            'page' => $pageTarget,
            'variants_deleted' => $deletedCount,
        ]);

        $this->logImageCleanup($entityId, basename($oldImageUrl), $pageTarget, $deletedCount);
    }

    protected function logInsertCleanup(int $entityId, string $pageTarget): void
    {
        $this->logger?->info('Entity created, cache cleaned', [
            'entity_type' => $this->getEntityType(),
            'entity_id' => $entityId,
            'page' => $pageTarget,
        ]);
    }

    protected function logDeleteCleanup(int $entityId, string $pageTarget): void
    {
        $this->logger?->info('Entity deleted, cache cleaned', [
            'entity_type' => $this->getEntityType(),
            'entity_id' => $entityId,
            'page' => $pageTarget,
        ]);
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

    abstract protected function getCacheManager(): HtmlSectionCacheManager;

    abstract protected function getServiceClass(): string;

    abstract protected function getEntityType(): string;

    private function resolveOperation(array $payload): string
    {
        $operation = $payload['operation'] ?? null;

        if (!in_array($operation, ['INSERT', 'UPDATE', 'DELETE'], true)) {
            throw new EventRuntimeException(
                sprintf('Unsupported or missing operation: "%s"', (string) $operation),
            );
        }

        return $operation;
    }
}