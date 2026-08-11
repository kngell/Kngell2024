<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class PageTracker implements PageTrackerInterface
{
    private const DEFAULT_TTL = 86400; // 24 hours

    private string $prefix;

    public function __construct(
        private CacheInterface $trackerCache,
        private ?LoggerInterface $logger = null,
        private int $ttl = self::DEFAULT_TTL,
    ) {
        $this->prefix = 'page_tracker_';
    }

    public function track(string $entityIdentifier, int $page, int $perPage): void
    {
        $trackerKey = $this->generateTrackerKey($entityIdentifier);
        $trackedPages = $this->trackerCache->get($trackerKey) ?? [];
        $pageKey = $this->createPageKey($page, $perPage);

        if (!in_array($pageKey, $trackedPages, true)) {
            $trackedPages[] = $pageKey;
            $this->trackerCache->set($trackerKey, $trackedPages, $this->ttl);

            $this->logger?->debug('Page tracked', [
                'entity_id' => $entityIdentifier,
                'page_key' => $pageKey,
                'total_tracked' => count($trackedPages),
            ]);
        }
    }

    public function getEntityPages(string $entityIdentifier): array
    {
        $trackerKey = $this->generateTrackerKey($entityIdentifier);
        $trackedPages = $this->trackerCache->get($trackerKey) ?? [];

        $pages = [];
        foreach ($trackedPages as $pageKey) {
            if (str_contains($pageKey, '_')) {
                [$page, $perPage] = explode('_', $pageKey);
                $pages[] = [
                    'page' => (int) $page,
                    'perPage' => (int) $perPage,
                    'key' => $pageKey,
                ];
            }
        }

        return $pages;
    }

    public function clearPages(
        string $entityIdentifier,
        PaginationCacheManager $paginationCache,
    ): array {
        $trackerKey = $this->generateTrackerKey($entityIdentifier);
        $trackedPages = $this->trackerCache->get($trackerKey) ?? [];
        $clearedPages = [];

        foreach ($trackedPages as $pageKey) {
            if (str_contains($pageKey, '_')) {
                [$page, $perPage] = explode('_', $pageKey);
                if ($paginationCache->clearPage((int) $page, (int) $perPage)) {
                    $clearedPages[] = $pageKey;
                }
            }
        }

        $this->trackerCache->delete($trackerKey);

        $this->logger?->info('Cleared pages for entity', [
            'entity_id' => $entityIdentifier,
            'cleared_pages' => $clearedPages,
        ]);

        return $clearedPages;
    }

    public function clearAllPages(
        string $entityIdentifier,
        PaginationCacheManager $paginationCache,
    ): array {
        $trackerKey = $this->generateTrackerKey($entityIdentifier);
        $trackedPages = $this->trackerCache->get($trackerKey) ?? [];

        if (!empty($trackedPages)) {
            $pageNumbers = [];

            foreach ($trackedPages as $pageKey) {
                if (str_contains($pageKey, '_')) {
                    [$page] = explode('_', $pageKey);
                    $pageNumbers[] = (int) $page;
                }
            }

            if (!empty($pageNumbers)) {
                $startPage = min($pageNumbers);
                $this->logger?->info(
                    "Entity deleted. Clearing all pages from {$startPage} onwards to prevent shifting issues.",
                );

                foreach ($trackedPages as $pageKey) {
                    if (str_contains($pageKey, '_')) {
                        [$page, $perPage] = explode('_', $pageKey);
                        $paginationCache->clearPage((int) $page, (int) $perPage);
                    }
                }
            }
        }

        $this->trackerCache->delete($trackerKey);
        return $trackedPages;
    }

    public function untrack(string $entityIdentifier, int $page, int $perPage): void
    {
        $trackerKey = $this->generateTrackerKey($entityIdentifier);
        $trackedPages = $this->trackerCache->get($trackerKey) ?? [];
        $pageKey = $this->createPageKey($page, $perPage);

        $filteredPages = array_filter(
            $trackedPages,
            fn (string $key): bool => $key !== $pageKey,
        );

        if (empty($filteredPages)) {
            $this->trackerCache->delete($trackerKey);
        } else {
            $this->trackerCache->set(
                $trackerKey,
                array_values($filteredPages),
                $this->ttl,
            );
        }

        $this->logger?->debug('Entity page untracked', [
            'entity_id' => $entityIdentifier,
            'page_key' => $pageKey,
        ]);
    }

    public function cleanOrphanedTrackers(): int
    {
        // Trackers auto-expire based on TTL
        // Implement cache-specific cleanup if needed
        $this->logger?->debug('Tracker cleanup: relying on TTL-based expiration');
        return 0;
    }

    private function generateTrackerKey(string $entityIdentifier): string
    {
        // Create a safe cache key from any identifier
        $safeKey = preg_replace('/[^a-zA-Z0-9_-]/', '_', $entityIdentifier);
        return $this->prefix . $safeKey;
    }

    private function createPageKey(int $page, int $perPage): string
    {
        return "{$page}_{$perPage}";
    }
}