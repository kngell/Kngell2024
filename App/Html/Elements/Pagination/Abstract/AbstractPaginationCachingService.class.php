<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

abstract class AbstractPaginationCachingService implements PaginationCachingServiceInterface
{
    protected const int PAGE_TTL = 300;
    protected const int COUNT_TTL = 300;
    protected const int ENTITY_TTL = 3600;

    protected string $entityClass;

    public function __construct(
        protected EntityCachingServiceInterface $entityCache,
        protected CacheInterface $pageCache,
        protected EntityCacheKeyGeneratorInterface $keyGenerator,
        protected ?LoggerInterface $logger = null,
    ) {
        $this->validateEntityClass();
    }

    public function getEntities(int $page, int $perPage, bool $forceRefresh = false): array
    {
        $pageCacheKey = $this->generatePageCacheKey($page, $perPage);
        $entityKeys = [];

        $cachedPage = $this->pageCache->get($pageCacheKey);

        if (is_array($cachedPage) && isset($cachedPage['entity_keys'])) {
            $entityKeys = $cachedPage['entity_keys'];
            $this->logDebug('L1 cache hit for page', ['page' => $page, 'perPage' => $perPage]);
        } else {
            $identifiers = $this->getAllEntityKeys($page, $perPage);

            if (empty($identifiers)) {
                $this->cacheEmptyPage($pageCacheKey);
                return [];
            }

            foreach ($identifiers as $identifier) {
                $entityKeys[] = $this->generateEntityCacheKey((string) $identifier);
            }

            $this->pageCache->set($pageCacheKey, [
                'entity_keys' => $entityKeys,
                'identifiers' => $identifiers,
                'timestamp' => time(),
                'count' => count($entityKeys),
            ], self::PAGE_TTL);

            $this->logDebug('L1 cache miss, fetched from DB', [
                'page' => $page,
                'perPage' => $perPage,
                'count' => count($identifiers),
            ]);
        }

        if (empty($entityKeys)) {
            return [];
        }

        $cachedEntities = $this->entityCache->getEntities($entityKeys, $this->entityClass);

        $retrievedKeys = [];
        foreach ($cachedEntities as $entity) {
            $retrievedKeys[] = $this->entityCache->generateCacheKey($entity);
        }
        $missingKeys = array_diff($entityKeys, $retrievedKeys);

        $finalEntities = $cachedEntities;

        if (!empty($missingKeys)) {
            $missingIdentifiers = $this->extractIdentifiersFromCacheKeys($missingKeys);
            $missingEntities = $this->getEntitiesByKeys($missingIdentifiers);

            foreach ($missingEntities as $entity) {
                $this->cacheEntity($entity);
                $finalEntities[] = $entity;
            }

            $this->logDebug('L2 cache partial hit', [
                'total' => count($entityKeys),
                'cached' => count($cachedEntities),
                'missing' => count($missingEntities),
            ]);
        } else {
            $this->logDebug('L2 cache full hit', ['count' => count($cachedEntities)]);
        }

        return $this->reorderEntitiesByKeys($finalEntities, $entityKeys);
    }

    public function getEntity(string $identifier, bool $forceRefresh = false): ?Entity
    {
        $cacheKey = $this->generateEntityCacheKey($identifier);

        if (!$forceRefresh) {
            $cachedEntity = $this->entityCache->getEntity($cacheKey, $this->entityClass);
            if ($cachedEntity !== null) {
                $this->logDebug('Entity cache hit', [
                    'entity' => $this->entityClass,
                    'identifier' => $identifier,
                ]);
                return $cachedEntity;
            }
        }

        $entity = $this->getEntityFromSource($identifier);

        if ($entity !== null) {
            $this->cacheEntity($entity);
        }

        return $entity;
    }

    public function cacheEntity(Entity $entity): bool
    {
        if (!$entity instanceof $this->entityClass) {
            throw new InvalidArgumentException(sprintf(
                'Entity must be an instance of %s, %s given',
                $this->entityClass,
                get_class($entity),
            ));
        }

        return $this->entityCache->cacheEntity($entity, self::ENTITY_TTL);
    }

    public function getTotalCount(bool $forceRefresh = false): int
    {
        $cacheKey = $this->generateCountCacheKey();

        if (!$forceRefresh) {
            $cachedCount = $this->pageCache->get($cacheKey);
            if (is_numeric($cachedCount)) {
                return (int) $cachedCount;
            }
        }

        $count = $this->getTotalCountFromSource();
        $this->pageCache->set($cacheKey, $count, self::COUNT_TTL);

        return $count;
    }

    public function invalidateEntity(string $identifier): void
    {
        $cacheKey = $this->generateEntityCacheKey($identifier);
        $this->entityCache->invalidateEntity($cacheKey);

        // Note: We cannot easily invalidate pages containing this entity
        // without cache tags or entity→page mapping
        $this->logDebug('Entity cache invalidated', [
            'entity' => $this->entityClass,
            'identifier' => $identifier,
        ]);
    }

    public function invalidateAll(): void
    {
        // Clear count cache
        $countKey = $this->generateCountCacheKey();
        $this->pageCache->delete($countKey);

        // Note: Cannot clear all entity cache without tags
        // Cannot clear all page cache without pattern matching
        $this->logDebug('Cache invalidated', ['entity' => $this->entityClass]);
    }

    public function cacheEntities(array $entities): int
    {
        $validEntities = array_filter($entities, fn ($e) => $e instanceof $this->entityClass);

        if (empty($validEntities)) {
            return 0;
        }

        $this->entityCache->cacheEntities($validEntities, self::ENTITY_TTL);
        return count($validEntities);
    }

    public function warmPageCache(int $page, int $perPage): bool
    {
        try {
            $this->getEntities($page, $perPage);
            return true;
        } catch (Throwable $e) {
            $this->logError('Failed to warm page cache', [
                'entity' => $this->entityClass,
                'page' => $page,
                'perPage' => $perPage,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    // =================== ABSTRACT METHODS ===================

    abstract protected function getAllEntityKeys(int $page, int $perPage): array;

    abstract protected function getEntitiesByKeys(array $identifiers): array;

    abstract protected function getEntityIdentifier(Entity $entity): string;

    abstract protected function getTotalCountFromSource(): int;

    abstract protected function generatePageCacheKey(int $page, int $perPage): string;

    abstract protected function generateEntityCacheKey(string $identifier): string;

    abstract protected function generateCountCacheKey(): string;

    // =================== PROTECTED HELPERS ===================

    protected function getEntityFromSource(string $identifier): ?Entity
    {
        $entities = $this->getEntitiesByKeys([$identifier]);
        return $entities[0] ?? null;
    }

    protected function logDebug(string $message, array $context = []): void
    {
        $this->logger?->debug($message, $context);
    }

    protected function logError(string $message, array $context = []): void
    {
        $this->logger?->error($message, $context);
    }

    protected function extractIdentifiersFromCacheKeys(array $cacheKeys): array
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

    // =================== MAIN PUBLIC METHODS ===================

    // =================== PRIVATE IMPLEMENTATION ===================

    private function reorderEntitiesByKeys(array $entities, array $orderedKeys): array
    {
        if (empty($entities) || empty($orderedKeys)) {
            return [];
        }

        // Create map of entity cache key to entity
        $entityMap = [];
        foreach ($entities as $entity) {
            $cacheKey = $this->entityCache->generateCacheKey($entity);
            $entityMap[$cacheKey] = $entity;
        }

        // Build ordered list
        $orderedEntities = [];
        foreach ($orderedKeys as $key) {
            if (isset($entityMap[$key])) {
                $orderedEntities[] = $entityMap[$key];
            }
        }

        return $orderedEntities;
    }

    private function cacheEmptyPage(string $pageCacheKey): void
    {
        $this->pageCache->set($pageCacheKey, [
            'entity_keys' => [],
            'identifiers' => [],
            'timestamp' => time(),
            'count' => 0,
        ], self::PAGE_TTL);
    }

    private function validateEntityClass(): void
    {
        if (!class_exists($this->entityClass)) {
            throw new RuntimeException(sprintf(
                'Entity class %s does not exist',
                $this->entityClass,
            ));
        }

        if (!is_subclass_of($this->entityClass, Entity::class)) {
            throw new RuntimeException(sprintf(
                'Entity class %s must extend Entity',
                $this->entityClass,
            ));
        }
    }
}