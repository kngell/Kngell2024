<?php

declare(strict_types=1);

interface PageTrackerInterface
{
    /**
     * Track that an entity appears on a specific page.
     */
    public function track(string $entityIdentifier, int $page, int $perPage): void;

    /**
     * Get all pages where an entity is tracked.
     *
     * @return array<int, array{page: int, perPage: int, key: string}>
     */
    public function getEntityPages(string $entityIdentifier): array;

    /**
     * Clear all pages associated with an entity (for updates).
     *
     * @return array<string> List of cleared page keys
     */
    public function clearPages(
        string $entityIdentifier,
        PaginationCacheManager $paginationCache,
    ): array;

    /**
     * Clear all pages from start page onwards (for deletes to prevent shifting).
     *
     * @return array<string> List of cleared page keys
     */
    public function clearAllPages(
        string $entityIdentifier,
        PaginationCacheManager $paginationCache,
    ): array;

    /**
     * Remove specific page from tracking for an entity.
     */
    public function untrack(string $entityIdentifier, int $page, int $perPage): void;

    /**
     * Clean up expired/orphaned trackers.
     *
     * @return int Number of cleaned trackers
     */
    public function cleanOrphanedTrackers(): int;
}