<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class EntityIdsCacheManager
{
    private const int DEFAULT_TTL = 3600;

    public function __construct(
        private CacheInterface $cache,
        private ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @return array<int>
     */
    public function getIds(string $cacheKey, callable $loader, ?int $ttl = self::DEFAULT_TTL): array
    {
        $cached = $this->cache->get($cacheKey);

        if ($cached !== null) {
            $this->logDebug('Collection list cache hit', ['key' => $cacheKey]);
            return is_array($cached) ? $cached : [];
        }

        $this->logDebug('Collection list cache miss', ['key' => $cacheKey]);

        $ids = $loader();

        if (!empty($ids)) {
            $this->cache->set($cacheKey, $ids, $ttl);
        }

        return $ids;
    }

    public function invalidate(string $cacheKey): bool
    {
        return $this->cache->delete($cacheKey);
    }

    public function invalidateByPattern(string $pattern): bool
    {
        return $this->cache->deletePattern($pattern);
    }

    private function logDebug(string $message, array $context = []): void
    {
        $this->logger?->debug('[CollectionListCache] ' . $message, $context);
    }
}