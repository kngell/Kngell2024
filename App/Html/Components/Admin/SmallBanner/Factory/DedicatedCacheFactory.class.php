<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class DedicatedCacheFactory
{
    public function __construct(
        private CacheManager $cacheManager,
        private LoggerInterface $logger,
    ) {
    }

    public function createSearchCache(string $cacheFolder = ''): ProductSearchCache
    {
        $cache = $this->cacheManager->createCache($cacheFolder);
        return new ProductSearchCache($cache, $this->logger);
    }
}