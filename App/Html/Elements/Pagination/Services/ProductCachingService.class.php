<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class ProductCachingService extends AbstractPaginationCachingService
{
    public function __construct(
        EntityCachingServiceInterface $entityCache,
        CacheInterface $pageCache,
        EntityCacheKeyGeneratorInterface $keyGenerator,
        private ProductShowModel $productModel,
        ?LoggerInterface $logger = null,
    ) {
        $this->entityClass = ProductShow::class;
        parent::__construct($entityCache, $pageCache, $keyGenerator, $logger);
    }

    protected function getAllEntityKeys(int $page, int $perPage): array
    {
        $results = $this->productModel->getAllProductKeys($page, $perPage);
        $keyField = $this->productModel->getEntiKeyField();
        $keyField = $keyField ? 'p_' . $keyField : 'p_public_id';
        return array_column($results, $keyField);
    }

    protected function getEntitiesByKeys(array $identifiers): array
    {
        return $this->productModel->getProductsByKeys($identifiers);
    }

    protected function getEntityIdentifier(Entity $entity): string
    {
        if (!$entity instanceof ProductShow) {
            throw new InvalidArgumentException('Entity must be a ProductShow');
        }

        // Use the key generator's logic instead of manual UUID handling
        return $this->keyGenerator->getCacheIdentifier($entity);
    }

    protected function getTotalCountFromSource(): int
    {
        return $this->productModel->count();
    }

    protected function generatePageCacheKey(int $page, int $perPage): string
    {
        // Use key generator for consistent sanitization
        $className = $this->keyGenerator->normalizeClassName($this->entityClass);
        return "page_{$className}_{$page}_{$perPage}";
    }

    protected function generateEntityCacheKey(string $identifier): string
    {
        return $this->keyGenerator->getCacheKeyFromIdentifier($this->entityClass, $identifier);
    }

    protected function generateCountCacheKey(): string
    {
        $className = $this->keyGenerator->normalizeClassName($this->entityClass);
        return "count_{$className}";
    }
}