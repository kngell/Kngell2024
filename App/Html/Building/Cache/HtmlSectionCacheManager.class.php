<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class HtmlSectionCacheManager
{
    public function __construct(
        private CacheInterface $pageCache,
        private EntityCacheManager $entityCache,
        private int $pageTtl = 3600,
        private int $entityTtl = 3600,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @template T of Entity
     *
     * @param string $page
     * @param string $serviceClass The service class name (for cache key)
     * @param callable(string): ?T $pageLoader Loader that fetches entity by page
     * @param callable(string): ?T $idLoader Loader that fetches entity by ID
     *
     * @return T|null
     */
    public function getEntityForPage(
        string $page,
        string $serviceClass,
        callable $pageLoader,
        callable $idLoader,
    ): ?Entity {
        $entityId = $this->getPageEntityId($page, $serviceClass);

        if ($entityId) {
            $entity = $this->entityCache->getEntity(
                $entityId,
                fn ($id) => $idLoader($id),
            );

            if ($entity) {
                $this->logDebug('Entity cache hit', ['page' => $page, 'entityId' => $entityId]);
                return $entity;
            }
        }
        $this->logDebug('Page cache miss', ['page' => $page]);
        $entity = $pageLoader($page);

        if ($entity) {
            $this->entityCache->cacheEntity($entity, $this->entityTtl);

            // Cache the page→ID mapping
            $this->cachePageEntityId($page, $serviceClass, $entity->getEntityPrimarykeyValue());
        }

        return $entity;
    }

    /**
     * @template T of Entity
     *
     * @param string $page
     * @param string $serviceClass The service class name (for cache key)
     * @param callable(string): array<T> $pageLoader Loader that fetches entities by page
     * @param callable(array): array<T> $idsLoader Loader that fetches entities by IDs
     *
     * @return array<T>
     */
    public function getEntitiesForPage(
        string $page,
        string $serviceClass,
        callable $pageLoader,
        callable $idsLoader,
    ): array {
        $entityIds = $this->getPageEntityIds($page, $serviceClass);

        if ($entityIds !== null) {
            // Level 2: Get entities by IDs from entity cache
            $entities = $this->entityCache->getEntities(
                $entityIds,
                fn ($missingIds) => $idsLoader($missingIds),
            );

            if (!empty($entities)) {
                $this->logDebug('Page entities cache hit', [
                    'page' => $page,
                    'count' => count($entities),
                ]);
                return $entities;
            }
        }

        // Cache miss - load from page loader
        $this->logDebug('Page cache miss', ['page' => $page]);
        $entities = $pageLoader($page);
        if (!empty($entities)) {
            // Cache each entity by ID
            foreach ($entities as $entity) {
                $this->entityCache->cacheEntity($entity, $this->entityTtl);
            }

            // Cache the page→IDs mapping
            $entityIds = array_map(fn ($e) => $e->getEntityPrimarykeyValue(), $entities);
            $this->cachePageEntityIds($page, $serviceClass, $entityIds);
        }

        return $entities;
    }

    public function invalidatePage(string $page, string $serviceClass): bool
    {
        $cacheKey = $this->getPageCacheKey($page, $serviceClass);
        return $this->pageCache->delete($cacheKey);
    }

    public function invalidateEntity(Entity $entity): bool
    {
        return $this->entityCache->invalidateEntity((string) $entity->getEntityPrimarykeyValue());
    }

    /**
     * Invalidate multiple entities at once.
     *
     * @param array<Entity> $entities
     */
    public function invalidateEntities(array $entities): bool
    {
        $success = true;
        foreach ($entities as $entity) {
            if (!$this->invalidateEntity($entity)) {
                $success = false;
            }
        }
        return $success;
    }

    public function invalidateAllPages(string $serviceClass): bool
    {
        $pattern = $this->getSectionPattern($serviceClass);
        return $this->pageCache->deletePattern($pattern);
    }

    public function getCachedPages(string $serviceClass): array
    {
        $pattern = $this->getSectionPattern($serviceClass);
        return $this->pageCache->getKeys($pattern) ?? [];
    }

    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        return $this->pageCache->remember($key, $callback, $ttl);
    }

    public function getStatistics(string $serviceClass): array
    {
        // Implementation depends on your cache system
        // Could return hits/misses, size, etc.
        return [
            'section' => $serviceClass,
            'keys' => [], // Would need cache introspection
        ];
    }

    public function getPageCacheKey(string $page, string $serviceClass): string
    {
        // Remove namespace and clean up
        $className = str_replace('\\', '_', $serviceClass);
        $normalizedPage = $this->normalize($page);
        return "page_{$className}_{$normalizedPage}";
    }

    private function getPageEntityId(string $page, string $serviceClass): null|int|string
    {
        $cacheKey = $this->getPageCacheKey($page, $serviceClass);
        return $this->pageCache->get($cacheKey);
    }

    private function getPageEntityIds(string $page, string $serviceClass): ?array
    {
        $cacheKey = $this->getPageCacheKey($page, $serviceClass);
        return $this->pageCache->get($cacheKey);
    }

    private function cachePageEntityId(string $page, string $serviceClass, string|int $entityId): void
    {
        $cacheKey = $this->getPageCacheKey($page, $serviceClass);
        $this->pageCache->set($cacheKey, (string) $entityId, $this->pageTtl);
    }

    private function cachePageEntityIds(string $page, string $serviceClass, array $entityIds): void
    {
        $cacheKey = $this->getPageCacheKey($page, $serviceClass);
        $this->pageCache->set($cacheKey, $entityIds, $this->pageTtl);
    }

    private function normalize(string $value): string
    {
        $normalized = preg_replace('/[^a-zA-Z0-9]/', '_', $value);
        $normalized = preg_replace('/_+/', '_', $normalized);
        return trim($normalized, '_');
    }

    private function logDebug(string $message, array $context = []): void
    {
        $this->logger?->debug('[HtmlPageCache] ' . $message, $context);
    }

    private function getSectionPattern(string $serviceClass): string
    {
        $className = str_replace('\\', '_', $serviceClass);
        return "page_{$className}_*";
    }
}