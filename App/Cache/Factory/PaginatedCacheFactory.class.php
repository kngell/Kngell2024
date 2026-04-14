<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class PaginatedCacheFactory
{
    private string $baseCachePath = '';
    private ?ProductPageTracker $productPageTracker = null;

    public function __construct(
        private EntityDataSerializerInterface $entityDataSerializer,
        protected EntityCacheKeyGeneratorInterface $keyGenerator,
        protected ?LoggerInterface $logger,
        private SmartSerializerInterface $serializer,
    ) {
        if (empty($this->baseCachePath)) {
            $this->baseCachePath = DS . 'storage' . DS . 'cache' . DS;
        }
    }

    /**
     * @template T of Entity
     *
     * @param class-string<T> $entityClass
     * @param class-string<AbstractPaginatedCacheService<T>> $cacheServiceClass
     *
     * @return AbstractPaginatedCacheService<T>
     */
    public function create(
        Model $model,
        string $entityClass,
        string $cacheServiceClass,
        string $cacheFolder,
    ): AbstractPaginatedCacheService {
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

        $entityCacheManagerService = new EntityCacheManager(
            $entityCachingService,
            $this->keyGenerator,
            $entityClass,
            $this->logger,
        );

        $paginationCache = new PaginationCacheManager(
            $entityCacheManager->createCache('pages'),
            $this->keyGenerator,
            $entityClass,
            $this->logger,
        );

        // Special handling for Product cache with tracker
        if ($entityClass === ProductShow::class && $cacheServiceClass === PaginatedProductCache::class) {
            return new PaginatedProductCache(
                $entityCacheManagerService,
                $paginationCache,
                $model,
                $this->getProductPageTracker(),
                $this->logger,
            );
        }

        // For other entity types (Order, Post, etc.) without tracker
        return new $cacheServiceClass(
            $entityCacheManagerService,
            $paginationCache,
            $model,
            $this->logger,
        );
    }

    public function createProductCache(ProductShowModel $model): PaginatedProductCache
    {
        $cache = $this->create(
            $model,
            ProductShow::class,
            PaginatedProductCache::class,
            'products',
        );

        if (!$cache instanceof PaginatedProductCache) {
            throw new RuntimeException('Failed to create product cache');
        }

        return $cache;
    }

    public function createWarmer(): CacheWarmer
    {
        // The warmer is stateless, but we can ensure it's provided here
        // or simply injected into the listener via DI.
        return new CacheWarmer();
    }

    private function getProductPageTracker(): ProductPageTracker
    {
        if ($this->productPageTracker === null) {
            // Create a dedicated cache instance for trackers
            $trackerCacheManager = new CacheManager(
                $this->serializer,
                $this->baseCachePath,
                'trackers',
            );

            $this->productPageTracker = new ProductPageTracker(
                $trackerCacheManager->createCache('product_pages'),
                $this->logger,
            );
        }

        return $this->productPageTracker;
    }
}