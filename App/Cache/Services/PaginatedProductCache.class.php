<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class PaginatedProductCache extends AbstractPaginatedCacheService
{
    public function __construct(
        EntityCacheManager $entityCache,
        PaginationCacheManager $paginationCache,
        private ProductShowModel $productModel,
        private ProductPageTracker $pageTracker,
        ?LoggerInterface $logger = null,
    ) {
        $this->entityClass = ProductShow::class;
        parent::__construct($entityCache, $paginationCache, $logger);
    }

    public function getEntities(int $page, int $perPage, bool $forceRefresh = false): array
    {
        $products = parent::getEntities($page, $perPage, $forceRefresh);

        foreach ($products as $product) {
            $identifier = $this->getEntityIdentifier($product);
            $this->pageTracker->trackProductPage($identifier, $page, $perPage);
        }

        return $products;
    }

    public function invalidateEntity(string $identifier): bool
    {
        // Ensure identifier has 'p_' prefix
        if (strpos($identifier, 'p_') !== 0) {
            $identifier = 'p_' . $identifier;
        }
        $this->logDebug('Product cache invalidated', ['identifier' => $identifier]);
        return parent::invalidateEntity($identifier);
    }

    public function invalidateProductWithPages(string $identifier, bool $isDelete = false): array
    {
        if (strpos($identifier, 'p_') !== 0) {
            $identifier = 'p_' . $identifier;
        }

        // 1. Always invalidate the specific product data
        $this->invalidateEntity($identifier);

        if ($isDelete) {
            $tagName = 'pages_' . $this->paginationCacheManager->generateCountCacheKey();
            $this->paginationCacheManager->getPageCache()->invalidateTags([$tagName]);
            $this->paginationCacheManager->invalidateCount();

            return ['all_pages_cleared_via_tags'];
        } else {
            return $this->pageTracker->clearProductPages($identifier, $this->paginationCacheManager);
        }
    }

    public function clearPageCache(int $page, int $perPage): bool
    {
        return $this->paginationCacheManager->clearPage($page, $perPage);
    }

    public function getPageCache(): CacheInterface
    {
        return $this->paginationCacheManager->getPageCache();
    }

    public function getEntityIdentifier(Entity $entity): string
    {
        if (!($entity instanceof ProductShow || $entity instanceof Product)) {
            throw new InvalidArgumentException('Entity must be a ProductShow or Product');
        }
        return $this->paginationCacheManager->getCacheIdentifier($entity);
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

    protected function getTotalCountFromSource(): int
    {
        return $this->productModel->count();
    }

    protected function generatePageCacheKey(int $page, int $perPage): string
    {
        return $this->paginationCacheManager->generatePageCacheKey($page, $perPage);
    }

    protected function generateEntityCacheKey(string $identifier): string
    {
        return $this->paginationCacheManager->generateEntityCacheKey($identifier);
    }

    protected function generateCountCacheKey(): string
    {
        return $this->paginationCacheManager->generateCountCacheKey();
    }
}