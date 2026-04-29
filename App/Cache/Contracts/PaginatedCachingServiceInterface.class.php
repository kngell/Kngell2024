<?php

declare(strict_types=1);

interface PaginatedCachingServiceInterface
{
    public function getEntities(int $page, int $perPage, bool $forceRefresh = false): array;

    public function getEntity(string $identifier, bool $forceRefresh = false): ?Entity;

    public function cacheEntity(Entity $entity): bool;

    public function getTotalCount(bool $forceRefresh = false): int;

    public function invalidateEntity(string $identifier): bool;

    public function invalidateAll(): void;

    public function cacheEntities(array $entities): int;

    public function warmPageCache(int $page, int $perPage): bool;

    public function getEntityClass(): string;

    public function invalidateCount(): void;
}