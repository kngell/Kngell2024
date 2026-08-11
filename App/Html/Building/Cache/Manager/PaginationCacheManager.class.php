<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class PaginationCacheManager
{
    private const int PAGE_TTL = 300;
    private const int COUNT_TTL = 300;

    public function __construct(
        private CacheInterface $pageCache,
        private EntityCacheKeyGeneratorInterface $keyGenerator,
        private string $entityClass,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function getPageKeys(int $page, int $perPage, callable $fetchKeysCallback): array
    {
        $pageCacheKey = $this->generatePageCacheKey($page, $perPage);
        $cachedPage = $this->pageCache->get($pageCacheKey);

        if (is_array($cachedPage) && isset($cachedPage['entity_keys'])) {
            return [
                'entity_keys' => $cachedPage['entity_keys'],
                'identifiers' => $cachedPage['identifiers'] ?? [],
            ];
        }

        $identifiers = $fetchKeysCallback();
        if (empty($identifiers)) {
            $this->cacheEmptyPage($pageCacheKey);
            return ['entity_keys' => [], 'identifiers' => []];
        }

        $entityKeys = $this->generateEntityCacheKeys($identifiers);
        $tagName = 'pages_' . $this->keyGenerator->normalizeClassName($this->entityClass);

        $this->pageCache->setWithTags($pageCacheKey, [
            'entity_keys' => $entityKeys,
            'identifiers' => $identifiers,
            'timestamp' => time(),
            'count' => count($entityKeys),
        ], self::PAGE_TTL, [$tagName]);

        return [
            'entity_keys' => $entityKeys,
            'identifiers' => $identifiers,
        ];
    }

    public function invalidateAllPages(): void
    {
        $tagName = $this->getPageTag();
        $this->pageCache->invalidateTags([$tagName]);
        $this->logDebug('All page caches invalidated via tag', [
            'entity' => $this->entityClass,
            'tag' => $tagName,
        ]);
    }

    public function invalidateAll(): void
    {
        $this->invalidateCount();
        $this->invalidateAllPages();
    }

    public function getPageTag(): string
    {
        return 'pages_' . $this->keyGenerator->normalizeClassName($this->entityClass);
    }

    public function getTotalCount(callable $fetchCountCallback, bool $forceRefresh = false): int
    {
        $cacheKey = $this->generateCountCacheKey();

        if (!$forceRefresh) {
            $cachedCount = $this->pageCache->get($cacheKey);
            if (is_numeric($cachedCount)) {
                return (int) $cachedCount;
            }
        }

        $count = $fetchCountCallback();
        $this->pageCache->set($cacheKey, $count, self::COUNT_TTL);

        return $count;
    }

    public function invalidateCount(): void
    {
        $cacheKey = $this->generateCountCacheKey();
        $this->pageCache->delete($cacheKey);
        $this->logDebug('Count cache invalidated', ['entity' => $this->entityClass]);
    }

    public function clearAll(): void
    {
        $this->invalidateCount();
        // Note: Individual page cache entries cannot be cleared without a pattern delete capability
        // Consider implementing cache tags or adding a clearAllPages() method if needed
    }

    public function getPageCache(): CacheInterface
    {
        return $this->pageCache;
    }

    public function clearPage(int $page, int $perPage): bool
    {
        $cacheKey = $this->generatePageCacheKey($page, $perPage);
        return $this->pageCache->delete($cacheKey);
    }

    public function clearPages(array $pages, int $perPage): int
    {
        $count = 0;
        foreach ($pages as $page) {
            if ($this->clearPage($page, $perPage)) {
                $count++;
            }
        }
        return $count;
    }

    public function generateEntityCacheKeys(array $identifiers): array
    {
        $keys = [];
        foreach ($identifiers as $identifier) {
            $keys[] = $this->generateEntityCacheKey((string) $identifier);
        }
        return $keys;
    }

    public function generateEntityCacheKey(string $identifier): string
    {
        return $this->keyGenerator->getCacheKeyFromIdentifier($this->entityClass, $identifier);
    }

    public function getCacheIdentifier(Entity $entity): string
    {
        return $this->keyGenerator->getCacheIdentifier($entity);
    }

    public function generatePageCacheKey(int $page, int $perPage): string
    {
        $className = $this->keyGenerator->normalizeClassName($this->entityClass);
        return "page_{$className}_{$page}_{$perPage}";
    }

    public function generateCountCacheKey(): string
    {
        $className = $this->keyGenerator->normalizeClassName($this->entityClass);
        return "count_{$className}";
    }

    public function extractIdentifiersFromCacheKeys(array $cacheKeys): array
    {
        $identifiers = [];
        foreach ($cacheKeys as $cacheKey) {
            $identifier = $this->keyGenerator->extractIdentifierFromKey($cacheKey, $this->entityClass);
            if ($identifier !== null) {
                $identifiers[] = $identifier;
            }
        }
        return $identifiers;
    }

    private function cacheEmptyPage(string $pageCacheKey): void
    {
        $this->pageCache->setWithTags($pageCacheKey, [
            'entity_keys' => [],
            'identifiers' => [],
            'timestamp' => time(),
            'count' => 0,
        ], self::PAGE_TTL, [$this->getPageTag()]);
    }

    private function logDebug(string $message, array $context = []): void
    {
        $this->logger?->debug('[PaginationCache] ' . $message, $context);
    }
}