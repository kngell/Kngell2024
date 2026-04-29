<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class PaginatedCacheFactory
{
    private string $baseCachePath;

    public function __construct(
        private EntityDataSerializerInterface $entityDataSerializer,
        protected EntityCacheKeyGeneratorInterface $keyGenerator,
        protected ?LoggerInterface $logger,
        private SmartSerializerInterface $serializer,
    ) {
        $this->baseCachePath = DS . 'storage' . DS . 'cache' . DS;
    }

    public function create(
        PaginatedEntityAdapterInterface $adapter,
        ?string $cacheFolder = null,
        bool $enableTracker = true,
    ): PaginatedCacheService {
        $entityClass = $adapter->getEntityClass();
        $cacheFolder ??= $this->resolveCacheFolder($entityClass);

        // Create base cache infrastructure
        $entityCacheManager = $this->createEntityCacheManager($cacheFolder, $entityClass);
        $paginationCache = $this->createPaginationCache($cacheFolder, $entityClass);

        // Create optional page tracker
        $pageTracker = null;
        if ($enableTracker) {
            $pageTracker = $this->createPageTracker();
        }

        return new PaginatedCacheService(
            $entityCacheManager,
            $paginationCache,
            $adapter,
            $pageTracker,
            $this->logger,
        );
    }

    private function createEntityCacheManager(
        string $cacheFolder,
        string $entityClass,
    ): EntityCacheManager {
        $entityCacheManager = new CacheManager(
            $this->serializer,
            $this->baseCachePath,
            $cacheFolder,
        );

        $entityCachingService = new EntityCachingService(
            $entityCacheManager->createCache('entities'),
            $this->entityDataSerializer,
            $this->keyGenerator,
        );

        return new EntityCacheManager(
            $entityCachingService,
            $this->keyGenerator,
            $entityClass,
            $this->logger,
        );
    }

    private function createPaginationCache(
        string $cacheFolder,
        string $entityClass,
    ): PaginationCacheManager {
        $entityCacheManager = new CacheManager(
            $this->serializer,
            $this->baseCachePath,
            $cacheFolder,
        );

        return new PaginationCacheManager(
            $entityCacheManager->createCache('pages'),
            $this->keyGenerator,
            $entityClass,
            $this->logger,
        );
    }

    private function createPageTracker(): PageTracker
    {
        $trackerCacheManager = new CacheManager(
            $this->serializer,
            $this->baseCachePath,
            'trackers',
        );

        return new PageTracker(
            $trackerCacheManager->createCache('page_trackers'),
            $this->logger,
        );
    }

    private function resolveCacheFolder(string $entityClass): string
    {
        $reflection = new ReflectionClass($entityClass);
        return strtolower($reflection->getShortName()) . 's';
    }
}