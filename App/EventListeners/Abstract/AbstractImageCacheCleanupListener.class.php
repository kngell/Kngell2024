<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

abstract class AbstractSectionCacheCleanupListener implements EventListenerInterface
{
    use BlockTypeTrait;

    protected ?BlockType $blockType = null;

    public function __construct(
        protected ImageCacheFactory $imageCacheFactory,
        protected ?LoggerInterface $logger = null,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $operation = $this->resolveOperation($payload);
        $deleteOption = $payload['deletion_option'] ?? null;
        $this->blockType = $this->resolveBlockType($event);

        if ($operation === SqlStatement::UPDATE->value && $deleteOption === 'archive') {
            $this->handleDelete($event);
            return null;
        }

        match ($operation) {
            SqlStatement::INSERT->value => $this->handleInsert($event),
            SqlStatement::UPDATE->value => $this->handleUpdate($event),
            SqlStatement::DELETE->value => $this->handleDelete($event),
        };

        return null;
    }

    protected function handleInsert(EventInterface $event): void
    {
        $entityId = $event->getData()->getEntityId();
        $formData = $event->getData()->getFormData();
        $pageTarget = $formData['page_target'] ?? null;

        if (!$entityId) {
            return;
        }

        $cacheManager = $this->getCacheManager();
        $serviceClass = $this->getServiceClass();
        $cacheManager->invalidateAllPages($serviceClass);

        $this->logInsertCleanup($entityId, $pageTarget);
    }

    protected function handleUpdate(EventInterface $event): void
    {
        $payload = $event->getParams();
        $entityId = $event->getData()->getEntityId();
        $pageTarget = $this->getPageTarget($payload);
        $oldEntity = $event->getData()->getModelData()['old_entity_snapshot'] ?? null;
        $newImageUrl = $this->getNewImageUrl($payload);

        if (!$oldEntity || !$entityId) {
            return;
        }

        $oldImageUrl = $this->getOldImageUrl($oldEntity);

        // Clean up image cache if image(s) changed
        if ($oldImageUrl !== null && $oldImageUrl !== $newImageUrl) {
            $this->cleanupImageCache($oldImageUrl, $entityId, $pageTarget);
        }

        // Clean up entity and page cache
        // if (!$event->getData()->wasSkipped()) {
        $this->cleanupEntityAndPageCache($oldEntity, $pageTarget);
        // }
    }

    protected function handleDelete(EventInterface $event): void
    {
        $oldEntity = $event->getObject();
        $pageTarget = $event->getData()->getPageTarget();

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

    protected function cleanupEntityAndPageCache(Entity $entity, ?string $pageTarget): void
    {
        $cacheManager = $this->getCacheManager();
        $serviceClass = $this->getServiceClass();

        $cacheManager->invalidateEntity($entity);
        if ($pageTarget !== null) {
            $cacheManager->invalidatePage($pageTarget, $serviceClass);
        } else {
            $cacheManager->invalidateAllPages($serviceClass);
        }
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

    /**
     * Clean up image cache for single or multiple images.
     */
    protected function cleanupImageCache(null|string|array $oldImageUrl, int $entityId, ?string $pageTarget): void
    {
        $imageCache = $this->imageCacheFactory->create('images');
        $totalDeleted = 0;

        // Handle single image (string)
        if (is_string($oldImageUrl)) {
            $deletedCount = $imageCache->deleteImageCache($oldImageUrl);
            $totalDeleted += $deletedCount;

            $this->logger?->info('Image cache cleaned', [
                'entity_type' => $this->getEntityType(),
                'entity_id' => $entityId,
                'image' => basename($oldImageUrl),
                'page' => $pageTarget,
                'variants_deleted' => $deletedCount,
            ]);
        }
        // Handle multiple images (array)
        elseif (is_array($oldImageUrl)) {
            foreach ($oldImageUrl as $image) {
                // Handle both string URLs and array structures
                $imageUrl = is_array($image) ? ($image['url'] ?? null) : $image;

                if ($imageUrl && is_string($imageUrl)) {
                    $deletedCount = $imageCache->deleteImageCache($imageUrl);
                    $totalDeleted += $deletedCount;

                    $this->logger?->info('Image cache cleaned', [
                        'entity_type' => $this->getEntityType(),
                        'entity_id' => $entityId,
                        'image' => basename($imageUrl),
                        'page' => $pageTarget,
                        'variants_deleted' => $deletedCount,
                    ]);
                }
            }
        }

        $this->logImageCleanup($entityId, $oldImageUrl, $pageTarget, $totalDeleted);
    }

    protected function logInsertCleanup(int $entityId, ?string $pageTarget = null): void
    {
        $this->logger?->info('Entity created, cache cleaned', [
            'entity_type' => $this->getEntityType(),
            'entity_id' => $entityId,
            'page' => $pageTarget,
        ]);
    }

    protected function logDeleteCleanup(int $entityId, ?string $pageTarget): void
    {
        $this->logger?->info('Entity deleted, cache cleaned', [
            'entity_type' => $this->getEntityType(),
            'entity_id' => $entityId,
            'page' => $pageTarget,
        ]);
    }

    protected function logImageCleanup(int $entityId, null|string|array $image, ?string $pageTarget, int $deletedCount): void
    {
        $imageSummary = is_array($image)
            ? sprintf('%d images', count($image))
            : basename($image);

        error_log(sprintf(
            '%s cache cleaned: ID %d, Image(s): %s, Page: %s, Variants deleted: %d, Types: [images, entity, page, section]',
            $this->getEntityType(),
            $entityId,
            $imageSummary,
            $pageTarget,
            $deletedCount,
        ));
    }

    protected function getPageTarget(array $payload): ?string
    {
        return $payload['form_data']['page_target'] ?? null;
    }

    /**
     * Check if two image values are different (handles both string and array).
     */
    protected function imagesAreDifferent(null|string|array $old, null|string|array $new): bool
    {
        if ($old === $new) {
            return false;
        }

        // Compare arrays by serializing
        if (is_array($old) && is_array($new)) {
            return serialize($old) !== serialize($new);
        }

        return true;
    }

    abstract protected function getOldImageUrl(Entity $entity): null|string|array;

    abstract protected function getNewImageUrl(array $payload): null|string|array;

    abstract protected function getCacheManager(): HtmlSectionCacheManager;

    abstract protected function getServiceClass(): string;

    abstract protected function getEntityType(): string;

    private function resolveOperation(array $payload): string
    {
        $operation = $payload['operation'] ?? null;

        if (!in_array($operation, [
            SqlStatement::INSERT->value, SqlStatement::UPDATE->value, SqlStatement::DELETE->value,
        ], true)) {
            throw new EventRuntimeException(
                sprintf('Unsupported or missing operation: "%s"', (string) $operation),
            );
        }

        return $operation;
    }
}