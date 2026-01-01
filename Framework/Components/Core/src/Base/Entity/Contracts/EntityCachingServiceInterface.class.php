<?php

declare(strict_types=1);

interface EntityCachingServiceInterface
{
    /**
     * Cache an entity.
     */
    public function cacheEntity(Entity $entity, ?int $ttl = null): bool;

    /**
     * Get an entity from cache.
     */
    public function getEntity(string $cacheKey, string $entityClass): ?Entity;

    /**
     * Cache multiple entities.
     */
    public function cacheEntities(array $entities, ?int $ttl = null): bool;

    /**
     * Get multiple entities from cache.
     */
    public function getEntities(array $cacheKeys, string $entityClass): array;

    /**
     * Invalidate entity cache.
     */
    public function invalidateEntity(string $cacheKey): bool;

    /**
     * Check if entity is cached.
     */
    public function hasEntity(string $cacheKey): bool;

    public function getCache(): CacheInterface;

    public function generateCacheKey(Entity $entity): string;

    public function getDefaultTTL(): int;
}