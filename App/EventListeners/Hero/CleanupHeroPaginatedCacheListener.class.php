<?php

declare(strict_types=1);

class CleanupHeroPaginatedCacheListener implements EventListenerInterface
{
    private const OPERATION_INSERT = 'INSERT';
    private const OPERATION_UPDATE = 'UPDATE';
    private const OPERATION_DELETE = 'DELETE';

    public function __construct(
        protected readonly PaginatedCacheFactory $cacheFactory,
        private readonly HeroModel $model,
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $operation = $this->resolveOperation($payload);

        $cache = $this->cacheFactory->create(
            new HeroPaginatedAdapter($this->model),
            'hero',
            true,
        );

        match ($operation) {
            self::OPERATION_INSERT => $this->onInsert($cache),
            self::OPERATION_UPDATE => $this->onUpdate($cache, $this->resolveHeroId($payload)),
            self::OPERATION_DELETE => $this->onDelete($cache, $this->resolveHeroId($payload)),
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
    private function onUpdate(PaginatedCacheService $cache, string $heroId): void
    {
        $cache->invalidateEntityWithPages($heroId, isDelete: false);
    }

    /**
     * Entity removed → invalidate entity + count + all pages.
     */
    private function onDelete(PaginatedCacheService $cache, string $heroId): void
    {
        $cache->invalidateEntityWithPages($heroId, isDelete: true);
    }

    private function resolveOperation(array $payload): string
    {
        $operation = $payload['operation'] ?? null;

        if (!in_array($operation, [
            self::OPERATION_INSERT,
            self::OPERATION_UPDATE,
            self::OPERATION_DELETE,
        ], true)) {
            throw new EventRuntimeException(
                sprintf('Unsupported or missing operation: "%s"', (string) $operation),
            );
        }

        return $operation;
    }

    private function resolveHeroId(array $payload): string
    {
        $heroId = $payload['hero_id'] ?? $payload['record']?->getEntityPrimarykeyValue() ?? null;

        if ($heroId === null) {
            throw new EventRuntimeException('No hero ID found for cache identifier.');
        }

        return (string) $heroId;
    }
}