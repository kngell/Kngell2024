<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class ProductPageTracker
{
    private const TRACKER_TTL = 86400;
    private const TRACKER_PREFIX = 'product_page_tracker_';

    public function __construct(
        private CacheInterface $trackerCache,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function trackProductPage(string $productIdentifier, int $page, int $perPage): void
    {
        $productId = $this->normalizeProductId($productIdentifier);
        $trackerKey = self::TRACKER_PREFIX . $productId;

        $trackedPages = $this->trackerCache->get($trackerKey) ?? [];
        $pageKey = $this->createPageKey($page, $perPage);

        if (!in_array($pageKey, $trackedPages, true)) {
            $trackedPages[] = $pageKey;
            $this->trackerCache->set($trackerKey, $trackedPages, self::TRACKER_TTL);

            $this->logger?->debug('Product page tracked', [
                'product_id' => $productId,
                'page_key' => $pageKey,
                'total_tracked' => count($trackedPages),
            ]);
        }
    }

    public function getProductPages(string $productIdentifier): array
    {
        $productId = $this->normalizeProductId($productIdentifier);
        $trackerKey = self::TRACKER_PREFIX . $productId;

        $trackedPages = $this->trackerCache->get($trackerKey) ?? [];
        $pages = [];

        foreach ($trackedPages as $pageKey) {
            [$page, $perPage] = explode('_', $pageKey);
            $pages[] = [
                'page' => (int) $page,
                'perPage' => (int) $perPage,
                'key' => $pageKey,
            ];
        }

        return $pages;
    }

    public function clearProductPages(
        string $productIdentifier,
        PaginationCacheManager $paginationCache,
        bool $isDelete = false,
    ): array {
        $productId = $this->normalizeProductId($productIdentifier);
        $trackerKey = self::TRACKER_PREFIX . $productId;

        $trackedPages = $this->trackerCache->get($trackerKey) ?? [];
        $clearedPages = [];

        if ($isDelete && !empty($trackedPages)) {
            $pageNumbers = array_map(fn ($key) => (int) explode('_', $key)[0], $trackedPages);
            $startPage = min($pageNumbers);
            $perPage = (int) explode('_', $trackedPages[0])[1];

            $this->logger?->info("Product deleted. Clearing all pages from $startPage onwards to prevent shifting issues.");

            foreach ($trackedPages as $pageKey) {
                [$page, $perPage] = explode('_', $pageKey);
                $paginationCache->clearPage((int) $page, (int) $perPage);
                $clearedPages[] = $pageKey;
            }
        } else {
            foreach ($trackedPages as $pageKey) {
                [$page, $perPage] = explode('_', $pageKey);
                if ($paginationCache->clearPage((int) $page, (int) $perPage)) {
                    $clearedPages[] = $pageKey;
                }
            }
        }

        $this->trackerCache->delete($trackerKey);
        return $clearedPages;
    }
    // public function clearProductPages(string $productIdentifier, PaginationCacheManager $paginationCache): array
    // {
    //     $productId = $this->normalizeProductId($productIdentifier);
    //     $trackerKey = self::TRACKER_PREFIX . $productId;

    //     $trackedPages = $this->trackerCache->get($trackerKey) ?? [];
    //     $clearedPages = [];

    //     foreach ($trackedPages as $pageKey) {
    //         [$page, $perPage] = explode('_', $pageKey);

    //         if ($paginationCache->clearPage((int) $page, (int) $perPage)) {
    //             $clearedPages[] = $pageKey;
    //         }
    //     }

    //     // Remove the tracker
    //     $this->trackerCache->delete($trackerKey);

    //     $this->logger?->debug('Product pages cleared', [
    //         'product_id' => $productId,
    //         'pages_cleared' => count($clearedPages),
    //         'pages' => $clearedPages,
    //     ]);

    //     return $clearedPages;
    // }

    public function untrackProductPage(string $productIdentifier, int $page, int $perPage): void
    {
        $productId = $this->normalizeProductId($productIdentifier);
        $trackerKey = self::TRACKER_PREFIX . $productId;

        $trackedPages = $this->trackerCache->get($trackerKey) ?? [];
        $pageKey = $this->createPageKey($page, $perPage);

        $filteredPages = array_filter($trackedPages, fn ($key) => $key !== $pageKey);

        if (empty($filteredPages)) {
            $this->trackerCache->delete($trackerKey);
        } else {
            $this->trackerCache->set($trackerKey, array_values($filteredPages), self::TRACKER_TTL);
        }

        $this->logger?->debug('Product page untracked', [
            'product_id' => $productId,
            'page_key' => $pageKey,
        ]);
    }

    public function cleanOrphanedTrackers(): int
    {
        // Implementation depends on your cache adapter
        // Some caches auto-expire based on TTL
        return 0;
    }

    private function normalizeProductId(string $identifier): string
    {
        // Remove 'p_' prefix if present
        return str_replace('p_', '', $identifier);
    }

    private function createPageKey(int $page, int $perPage): string
    {
        return "{$page}_{$perPage}";
    }
}