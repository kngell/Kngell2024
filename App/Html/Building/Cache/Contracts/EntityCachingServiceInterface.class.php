<?php

declare(strict_types=1);

interface EntityCachingServiceInterface
{
    public function cacheEntity(Entity $entity, ?int $ttl = null): bool;

    public function getEntity(string $cacheKey, string $entityClass): ?Entity;

    public function cacheEntities(array $entities, ?int $ttl = null): bool;

    public function getEntities(array $cacheKeys, string $entityClass): array;

    public function invalidateEntity(string $cacheKey): bool;

    public function hasEntity(string $cacheKey): bool;

    public function getCache(): CacheInterface;

    public function generateCacheKey(Entity $entity): string;

    public function getDefaultTTL(): int;
}