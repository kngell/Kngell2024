<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class PaginationCachingFactory
{
    public function __construct(
        private EntityCachingServiceInterface $entityCache,
        private CacheInterface $pageCache,
        protected EntityCacheKeyGeneratorInterface $keyGenerator,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function createForProduct(ProductShowModel $model): PaginationCachingServiceInterface
    {
        return new ProductCachingService(
            $this->entityCache,
            $this->pageCache,
            $this->keyGenerator,
            $model,
            $this->logger,
        );
    }

    public function createForPost(PostModel $model): PaginationCachingServiceInterface
    {
        return new PostCachingService(
            $this->entityCache,
            $this->pageCache,
            $model,
            $this->logger,
        );
    }

    public function create(string $entityClass, Model $model): PaginationCachingServiceInterface
    {
        return match ($entityClass) {
            ProductShow::class => new ProductCachingService(
                $this->entityCache,
                $this->pageCache,
                $this->keyGenerator,
                $model,
                $this->logger,
            ),
            Post::class => new PostCachingService(
                $this->entityCache,
                $this->pageCache,
                $model,
                $this->logger,
            ),
            default => throw new InvalidArgumentException("No caching service for $entityClass")
        };
    }
}