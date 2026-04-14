<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class EntityCacheManager
{
    private const int DEFAULT_ENTITY_TTL = 3600;

    public function __construct(
        private EntityCachingServiceInterface $entityCache,
        private EntityCacheKeyGeneratorInterface $keyGenerator,
        private string $entityClass,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function getEntity(string $identifier, ?callable $loader = null): ?Entity
    {
        $cacheKey = $this->generateCacheKey($identifier);

        // Try cache first
        $cachedEntity = $this->entityCache->getEntity($cacheKey, $this->entityClass);
        if ($cachedEntity !== null) {
            $this->logDebug('Entity cache hit', ['identifier' => $identifier]);
            return $cachedEntity;
        }

        // Load from source if loader provided
        if ($loader !== null) {
            $entity = $loader($identifier);
            if ($entity !== null) {
                $this->cacheEntity($entity);
                return $entity;
            }
        }

        return null;
    }

    public function getEntities(array $identifiers, ?callable $bulkLoader = null): array
    {
        if (empty($identifiers)) {
            return [];
        }

        // Generate cache keys
        $cacheKeys = $this->generateCacheKeys($identifiers);

        // Get from cache
        $cachedEntities = $this->entityCache->getEntities($cacheKeys, $this->entityClass);

        // Build map of retrieved entities
        $entityMap = [];
        foreach ($cachedEntities as $entity) {
            $key = $this->entityCache->generateCacheKey($entity);
            $entityMap[$key] = $entity;
        }

        // Find missing keys
        $foundKeys = array_keys($entityMap);
        $missingKeys = array_diff($cacheKeys, $foundKeys);

        $result = $cachedEntities;

        // Load missing entities if bulk loader provided
        if (!empty($missingKeys) && $bulkLoader !== null) {
            $missingIdentifiers = $this->extractIdentifiersFromCacheKeys($missingKeys);
            $missingEntities = $bulkLoader($missingIdentifiers);

            foreach ($missingEntities as $entity) {
                $this->cacheEntity($entity);
                $result[] = $entity;
            }

            $this->logDebug('Bulk load completed', [
                'total' => count($identifiers),
                'cached' => count($cachedEntities),
                'loaded' => count($missingEntities),
            ]);
        }

        // Reorder to match input order
        return $this->reorderEntitiesByIdentifiers($result, $identifiers);
    }

    public function cacheEntity(Entity $entity, ?int $ttl = null): bool
    {
        if (!$entity instanceof $this->entityClass) {
            throw new InvalidArgumentException(sprintf(
                'Entity must be an instance of %s, %s given',
                $this->entityClass,
                get_class($entity),
            ));
        }

        return $this->entityCache->cacheEntity($entity, $ttl ?? self::DEFAULT_ENTITY_TTL);
    }

    public function cacheEntities(array $entities, ?int $ttl = null): int
    {
        $validEntities = array_filter($entities, fn ($e) => $e instanceof $this->entityClass);

        if (empty($validEntities)) {
            return 0;
        }

        $this->entityCache->cacheEntities($validEntities, $ttl ?? self::DEFAULT_ENTITY_TTL);
        return count($validEntities);
    }

    public function invalidateEntity(string $identifier): bool
    {
        $cacheKey = $this->generateCacheKey($identifier);
        $this->logDebug('Entity cache invalidated', ['identifier' => $identifier]);
        return $this->entityCache->invalidateEntity($cacheKey);
    }

    public function invalidateEntities(array $identifiers): void
    {
        foreach ($identifiers as $identifier) {
            $this->invalidateEntity($identifier);
        }
    }

    public function generateCacheKey(string $identifier): string
    {
        return $this->keyGenerator->getCacheKeyFromIdentifier($this->entityClass, $identifier);
    }

    public function generateCacheKeys(array $identifiers): array
    {
        return array_map(
            fn ($id) => $this->generateCacheKey((string) $id),
            $identifiers,
        );
    }

    public function extractIdentifierFromCacheKey(string $cacheKey): ?string
    {
        return $this->keyGenerator->extractIdentifierFromKey($cacheKey, $this->entityClass);
    }

    public function clearAll(): bool
    {
        try {
            $prefix = $this->keyGenerator->getEntityPrefix($this->entityClass);
            $pattern = $prefix . '.*';

            $this->logDebug('Clearing entity caches with pattern', [
                'entityClass' => $this->entityClass,
                'prefix' => $prefix,
                'pattern' => $pattern,
            ]);

            $result = $this->entityCache->getCache()->deletePattern($pattern);

            $this->logDebug('Entity caches cleared successfully', [
                'entityClass' => $this->entityClass,
                'result' => $result,
            ]);

            return $result;
        } catch (Exception $e) {
            $this->logger?->error('Failed to clear entity caches', [
                'entityClass' => $this->entityClass,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    public function clearByIdentifiers(array $identifiers): int
    {
        $deleted = 0;

        foreach ($identifiers as $identifier) {
            $cacheKey = $this->keyGenerator->getCacheKeyFromIdentifier(
                $this->entityClass,
                (string) $identifier,
            );

            if ($this->entityCache->invalidateEntity($cacheKey)) {
                $deleted++;
            }
        }

        $this->logDebug('Cleared specific entity caches', [
            'entityClass' => $this->entityClass,
            'requested' => count($identifiers),
            'deleted' => $deleted,
        ]);

        return $deleted;
    }

    public function clearByPattern(string $pattern): bool
    {
        try {
            $fullPattern = $this->keyGenerator->getEntityPrefix($this->entityClass) . '.' . $pattern;

            $result = $this->entityCache->getCache()->deletePattern($fullPattern);

            $this->logDebug('Cleared cache by custom pattern', [
                'entityClass' => $this->entityClass,
                'pattern' => $fullPattern,
                'result' => $result,
            ]);

            return $result;
        } catch (Exception $e) {
            $this->logger?->error('Failed to clear cache by pattern', [
                'entityClass' => $this->entityClass,
                'pattern' => $pattern,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getStatistics(): array
    {
        $prefix = $this->keyGenerator->getEntityPrefix($this->entityClass);
        $pattern = $prefix . '.*';

        $keys = $this->entityCache->getCache()->getKeys($pattern);

        return [
            'entityClass' => $this->entityClass,
            'prefix' => $prefix,
            'cachedItems' => count($keys),
            'keys' => $keys,
            'cacheDriver' => get_class($this->entityCache->getCache()),
        ];
    }

    private function extractIdentifiersFromCacheKeys(array $cacheKeys): array
    {
        $identifiers = [];
        foreach ($cacheKeys as $cacheKey) {
            $identifier = $this->extractIdentifierFromCacheKey($cacheKey);
            if ($identifier !== null) {
                $identifiers[] = $identifier;
            }
        }
        return $identifiers;
    }

    private function reorderEntitiesByIdentifiers(array $entities, array $orderedIdentifiers): array
    {
        if (empty($entities) || empty($orderedIdentifiers)) {
            return [];
        }

        $entityMap = [];
        foreach ($entities as $entity) {
            $identifier = $this->keyGenerator->getCacheIdentifier($entity);
            $entityMap[$identifier] = $entity;
        }

        $orderedEntities = [];
        foreach ($orderedIdentifiers as $identifier) {
            if (isset($entityMap[$identifier])) {
                $orderedEntities[] = $entityMap[$identifier];
            }
        }

        return $orderedEntities;
    }

    private function logDebug(string $message, array $context = []): void
    {
        $context['entityClass'] = $this->entityClass;
        $this->logger?->debug('[EntityCache] ' . $message, $context);
    }
}