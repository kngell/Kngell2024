<?php

declare(strict_types=1);

class CleanupProductPaginatedCacheListener implements EventListenerInterface
{
    public function __construct(
        protected readonly PaginatedCacheFactory $cacheFactory,
        private readonly ProductShowModel $model,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $operation = $this->resolveOperation($payload);

        $cache = $this->cacheFactory->create(
            new ProductPaginatedAdapter($this->model),
            EntityCacheFolder::PRODUCT->value,
            true,
        );

        match ($operation) {
            'INSERT' => $this->onInsert($cache),
            'UPDATE' => $this->onUpdate($cache, $this->resolveId($payload)),
            'DELETE' => $this->onDelete($cache, $this->resolveId($payload)),
        };

        return null;
    }

    /**
     * New entity → count stale, page lists stale, but no entity to invalidate.
     */
    private function onInsert(PaginatedCacheService $cache): void
    {
        $cache->invalidateAll();
    }

    /**
     * Entity data changed → invalidate entity + tracked pages. Count unchanged.
     */
    private function onUpdate(PaginatedCacheService $cache, string $id): void
    {
        $cache->invalidateEntityWithPages($id);
    }

    /**
     * Entity removed → invalidate entity + count + all pages.
     */
    private function onDelete(PaginatedCacheService $cache, string $id): void
    {
        $cache->invalidateEntityAndAllPages($id);
    }

    private function resolveOperation(array $payload): string
    {
        $operation = $payload['operation'] ?? null;

        if (!in_array($operation, [
            'INSERT',
            'UPDATE',
            'DELETE',
        ], true)) {
            throw new EventRuntimeException(
                sprintf('Unsupported or missing operation: "%s"', (string) $operation),
            );
        }

        return $operation;
    }

    private function resolveId(array $payload): string
    {
        $id = $payload['pdt_id'] ?? $payload['record']?->getEntityPrimarykeyValue() ?? null;

        if ($id === null) {
            throw new EventRuntimeException('No ID found for cache identifier.');
        }

        return (string) $id;
    }
}