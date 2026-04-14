<?php

declare(strict_types=1);

final class EntityCachingService implements EntityCachingServiceInterface
{
    private const DEFAULT_TTL = 3600;

    public function __construct(
        private CacheInterface $cache,
        private EntityDataSerializerInterface $entityDataSerializer,
        private EntityCacheKeyGeneratorInterface $cacheKeyGenerator,
    ) {
    }

    // public function cacheEntity(Entity $entity, ?int $ttl = null): bool
    // {
    //     try {
    //         $cacheKey = $this->cacheKeyGenerator->getCacheKey($entity);
    //         $data = $this->entityDataSerializer->getData($entity);
    //         return $this->cache->set($cacheKey, $data, $ttl ?? self::DEFAULT_TTL);
    //     } catch (Throwable $e) {
    //         return false;
    //     }
    // }

    // Inside EntityCachingService.php

    public function cacheEntity(Entity $entity, ?int $ttl = null, array $tags = []): bool
    {
        try {
            $cacheKey = $this->cacheKeyGenerator->getCacheKey($entity);
            $data = $this->entityDataSerializer->getData($entity);

            if (empty($tags)) {
                $tags[] = 'type_' . str_replace('\\', '_', get_class($entity));
            }

            return $this->cache->setWithTags(
                $cacheKey,
                $data,
                $ttl ?? self::DEFAULT_TTL,
                $tags,
            );
        } catch (Throwable $e) {
            return false;
        }
    }

    public function getEntity(string $cacheKey, string $entityClass): ?Entity
    {
        $data = $this->cache->get($cacheKey);

        if ($data === null) {
            return null;
        }
        try {
            if (!is_array($data)) {
                return null;
            }
            // dd($data);
            return $this->entityDataSerializer->restoreData($data);
        } catch (Throwable $e) {
            return null;
        }
    }

    public function cacheEntities(array $entities, ?int $ttl = null): bool
    {
        $success = true;

        foreach ($entities as $entity) {
            if ($entity instanceof Entity) {
                $result = $this->cacheEntity($entity, $ttl);
                $success = $success && $result;
            }
        }

        return $success;
    }

    // public function getEntities(array $cacheKeys, string $entityClass): array
    // {
    //     $entities = [];

    //     foreach ($cacheKeys as $cacheKey) {
    //         $entity = $this->getEntity($cacheKey, $entityClass);
    //         if ($entity !== null) {
    //             $entities[] = $entity;
    //         }
    //     }

    //     return $entities;
    // }

    public function getEntities(array $cacheKeys, string $entityClass): array
    {
        $allData = $this->cache->getMultiple($cacheKeys);

        $entities = [];
        foreach ($allData as $data) {
            if ($data !== null && is_array($data)) {
                try {
                    $entities[] = $this->entityDataSerializer->restoreData($data);
                } catch (Throwable) {
                    continue;
                }
            }
        }

        return $entities;
    }

    public function invalidateEntity(string $cacheKey): bool
    {
        return $this->cache->delete($cacheKey);
    }

    public function hasEntity(string $cacheKey): bool
    {
        return $this->cache->exists($cacheKey);
    }

    public function generateCacheKey(Entity $entity): string
    {
        return $this->cacheKeyGenerator->getCacheKey($entity);
    }

    public function cacheEntitiesBatch(array $entities, ?int $ttl = null, ?callable $progressCallback = null): array
    {
        $results = [
            'total' => count($entities),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($entities as $index => $entity) {
            if (!$entity instanceof Entity) {
                $results['failed']++;
                continue;
            }

            try {
                $success = $this->cacheEntity($entity, $ttl);

                if ($success) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = [
                        'index' => $index,
                        'entity' => get_class($entity),
                        'message' => 'Cache operation failed',
                    ];
                }
            } catch (Throwable $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'index' => $index,
                    'entity' => get_class($entity),
                    'message' => $e->getMessage(),
                ];
            }

            if ($progressCallback) {
                $progressCallback($index + 1, $results['total'], $results);
            }
        }

        return $results;
    }

    public function getStats(): array
    {
        $keys = $this->cache->getKeys('entity_*');

        $stats = [
            'total_entities' => count($keys),
            'entity_types' => [],
            'oldest' => null,
            'newest' => null,
        ];

        $timestamps = [];

        foreach ($keys as $key) {
            $data = $this->cache->get($key);

            if (is_array($data) && isset($data['__entity_meta'])) {
                $meta = $data['__entity_meta'];
                $entityType = $meta['class'] ?? 'Unknown';
                $stats['entity_types'][$entityType] = ($stats['entity_types'][$entityType] ?? 0) + 1;

                if (isset($meta['timestamp'])) {
                    $timestamps[] = (int) $meta['timestamp'];
                }
            }
        }

        if (!empty($timestamps)) {
            $stats['oldest'] = min($timestamps);
            $stats['newest'] = max($timestamps);
        }

        return $stats;
    }

    // public function getStats(): array
    // {
    //     $keys = $this->cache->getKeys('entity_*');

    //     $stats = [
    //         'total_entities' => count($keys),
    //         'entity_types' => [],
    //         'oldest' => null,
    //         'newest' => null,
    //     ];

    //     $timestamps = [];

    //     foreach ($keys as $key) {
    //         if (preg_match('/^entity_([^_]+_[^_]+)/', $key, $matches)) {
    //             $entityType = str_replace('_', '\\', $matches[1]);
    //             $stats['entity_types'][$entityType] = ($stats['entity_types'][$entityType] ?? 0) + 1;
    //         }
    //         $data = $this->cache->get($key);
    //         if ($data) {
    //             try {
    //                 if (is_array($data) && isset($unserialized['__entity_meta']['timestamp'])) {
    //                     $timestamps[] = $unserialized['__entity_meta']['timestamp'];
    //                 }
    //             } catch (Throwable) {
    //             }
    //         }
    //     }

    //     if (!empty($timestamps)) {
    //         $stats['oldest'] = min($timestamps);
    //         $stats['newest'] = max($timestamps);
    //     }

    //     return $stats;
    // }

    /**
     * @return CacheInterface
     */
    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    public function getDefaultTTL(): int
    {
        return self::DEFAULT_TTL;
    }
}