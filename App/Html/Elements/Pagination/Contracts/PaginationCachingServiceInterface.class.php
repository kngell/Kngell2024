<?php

declare(strict_types=1);

interface PaginationCachingServiceInterface
{
    /**
     * @param int $page
     * @param int $perPage
     * @param bool $forceRefresh
     *
     * @return Entity[]
     */
    public function getEntities(int $page, int $perPage, bool $forceRefresh = false): array;

    /**
     * @param string $identifier
     * @param bool $forceRefresh
     *
     * @return null|Entity
     */
    public function getEntity(string $identifier, bool $forceRefresh = false): ?Entity;

    public function cacheEntity(Entity $entity): bool;

    public function getTotalCount(bool $forceRefresh = false): int;

    public function invalidateEntity(string $identifier): void;

    public function invalidateAll(): void;

    public function cacheEntities(array $entities): int;

    public function warmPageCache(int $page, int $perPage): bool;

    public function getEntityClass(): string;
}