<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

abstract class AbstractHtmlSectionCacheFactory
{
    protected const string BASE_CACHE_PATH = DS . 'storage' . DS . 'cache' . DS;

    public function __construct(
        protected EntityDataSerializerInterface $entityDataSerializer,
        protected EntityCacheKeyGeneratorInterface $cacheKeyGenerator,
        protected SmartSerializerInterface $serializer,
        protected LoggerInterface $logger,
    ) {
    }

    public function create(): HtmlSectionCacheManager
    {
        $cache = $this->createCacheInstance();

        $entityCache = new EntityCachingService(
            cache: $cache,
            entityDataSerializer: $this->entityDataSerializer,
            cacheKeyGenerator: $this->cacheKeyGenerator,
        );

        $cacheManager = new EntityCacheManager(
            entityCache: $entityCache,
            keyGenerator: $this->cacheKeyGenerator,
            entityClass: $this->entityClass(),
            logger: $this->logger,
        );

        return new HtmlSectionCacheManager(
            $cache,
            $cacheManager,
            $this->pageTTl(),
            $this->entityTtl(),
            $this->logger,
        );
    }

    /**
     * Create cache instance with proper folder structure.
     */
    protected function createCacheInstance(): CacheInterface
    {
        $cacheManager = new CacheManager(
            serializer: $this->serializer,
            baseCachePath: $this->baseCachePath(),
            cacheFolder: $this->cacheFolder(),
        );

        return $cacheManager->createCache($this->cacheSubFolder());
    }

    /**
     * Get the full base cache path.
     */
    protected function baseCachePath(): string
    {
        return self::BASE_CACHE_PATH;
    }

    protected function cacheSubFolder(): string
    {
        return 'entities';
    }

    abstract protected function cacheFolder(): string;

    abstract protected function entityClass(): string;

    abstract protected function pageTTl(): int;

    abstract protected function entityTtl(): int;
}