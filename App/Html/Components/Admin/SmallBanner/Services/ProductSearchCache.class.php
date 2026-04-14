<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class ProductSearchCache
{
    private array $cache = [];
    private array $ttlConfig = [
        'no_search' => 300,
        'search' => 60,
        'empty' => 30,
        'count' => 600,
        'by_ids' => 3600,
    ];

    public function __construct(
        private ?CacheInterface $externalCache,
        private LoggerInterface $logger,
    ) {
    }

    public function getSearchResult(string $cacheKey): null|int|array
    {
        if ($this->externalCache) {
            return $this->externalCache->get($cacheKey);
        }

        if (isset($this->cache[$cacheKey])) {
            $cached = $this->cache[$cacheKey];
            if (time() - $cached['time'] < $cached['ttl']) {
                return $cached['data'];
            }
            unset($this->cache[$cacheKey]);
        }

        return null;
    }

    public function setSearchResult(string $cacheKey, mixed $data, string $type = 'no_search'): void
    {
        $ttl = $this->ttlConfig[$type] ?? $this->ttlConfig['no_search'];

        if ($this->externalCache) {
            $this->externalCache->set($cacheKey, $data, $ttl);
            return;
        }

        $this->cache[$cacheKey] = [
            'data' => $data,
            'time' => time(),
            'ttl' => $ttl,
        ];
    }

    /**
     * Generate valid cache key (only a-z, 0-9, _, . and max 64 chars).
     */
    public function generateSearchKey(int $page, int $limit, string $search, array $columns): string
    {
        // Create short hashes
        $columnsHash = substr(md5(implode(',', $columns)), 0, 8);
        $searchHash = !empty($search) ? substr(md5($search), 0, 8) : 'empty';

        // Build key without hyphens, using underscores
        $key = sprintf(
            'prod_search_p%d_l%d_s%s_c%s',
            $page,
            $limit,
            $searchHash,
            $columnsHash,
        );

        // Ensure key is within length limit (max 64 chars)
        return substr($key, 0, 64);
    }

    /**
     * Generate count cache key (only a-z, 0-9, _, . and max 64 chars).
     */
    public function generateCountKey(string $search): string
    {
        $searchHash = !empty($search) ? substr(md5($search), 0, 16) : 'empty';
        $key = 'prod_count_' . $searchHash;
        return substr($key, 0, 64);
    }

    /**
     * Generate IDs cache key (only a-z, 0-9, _, . and max 64 chars).
     */
    public function generateIdsKey(array $ids): string
    {
        sort($ids);
        // Take first 5 IDs to create a unique hash
        $idString = implode('_', array_slice($ids, 0, 5));
        $idHash = substr(md5($idString), 0, 16);
        $key = 'prod_ids_' . $idHash;
        return substr($key, 0, 64);
    }

    /**
     * Generate single product cache key.
     */
    public function generateProductKey(int $productId): string
    {
        $key = 'prod_id_' . $productId;
        return substr($key, 0, 64);
    }

    /**
     * Get cached value by key.
     */
    public function get(string $key): ?array
    {
        return $this->getSearchResult($key);
    }

    /**
     * Set cached value by key.
     */
    public function set(string $key, mixed $data, string $type = 'no_search'): void
    {
        $this->setSearchResult($key, $data, $type);
    }

    public function invalidateByPattern(string $pattern): void
    {
        if ($this->externalCache) {
            $this->externalCache->deletePattern($pattern);
            return;
        }

        $this->cache = [];

        $this->logger->debug('Product search cache invalidated', ['pattern' => $pattern]);
    }

    public function invalidateAll(): void
    {
        if ($this->externalCache) {
            $this->externalCache->deletePattern('prod_*');
        } else {
            $this->cache = [];
        }

        $this->logger->info('All product search cache invalidated');
    }

    public function getCacheType(string $search, array $results): string
    {
        if (empty($search)) {
            return 'no_search';
        }

        if (empty($results)) {
            return 'empty';
        }

        return 'search';
    }
}