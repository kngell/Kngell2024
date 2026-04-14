<?php

declare(strict_types=1);

final class CacheWarmer
{
    private const DEFAULT_PAGES_TO_WARM = 3;
    private const DEFAULT_PER_PAGE = 20;

    public function __construct(
        private array $allowedPageSizes = [20, 50, 100],
    ) {
    }

    public function warm(
        PaginatedCachingServiceInterface $cacheService,
        int $pages = self::DEFAULT_PAGES_TO_WARM,
        int $perPage = self::DEFAULT_PER_PAGE,
    ): void {
        // We always warm the first page as a priority
        for ($i = 1; $i <= $pages; $i++) {
            $cacheService->getEntities(
                page: $i,
                perPage: $perPage,
                forceRefresh: true,
            );
        }
    }

    public function warmCommonViews(
        PaginatedCachingServiceInterface $cacheService,
        PaginationStateService $stateService,
    ): void {
        foreach ($stateService->getAllowedSizes() as $size) {
            $cacheService->getEntities(page: 1, perPage: $size, forceRefresh: true);
        }
    }
}