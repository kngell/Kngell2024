<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class ProductSearchService
{
    private const CACHE_FOLDER = 'product_search';

    private ProductSearchCache $cache;

    public function __construct(
        private ProductModel $productModel,
        private DedicatedCacheFactory $cacheFactory,
        private IconBuilder $iconBuilder,
        private LoggerInterface $logger,
    ) {
        $this->cache = $this->cacheFactory->createSearchCache(self::CACHE_FOLDER);
    }

    public function searchProducts(
        int $page = 1,
        int $limit = 20,
        string $search = '',
        array $columns = ['pdt_id', 'name', 'sku', 'short_description', 'main_image'],
        bool $forceRefresh = false,
    ): array {
        $cacheKey = $this->cache->generateSearchKey($page, $limit, $search, $columns);

        // Check cache if not forcing refresh
        if (!$forceRefresh) {
            $cached = $this->cache->getSearchResult($cacheKey);
            if ($cached !== null) {
                $this->logger->debug('Product search cache hit', [
                    'key' => $cacheKey,
                    'search' => $search,
                    'page' => $page,
                ]);
                return $cached;
            }
        }

        $this->logger->debug('Product search cache miss', [
            'key' => $cacheKey,
            'search' => $search,
            'page' => $page,
        ]);

        try {
            // Get total count with caching
            $total = $this->getTotalCount($search, $forceRefresh);

            // Get paginated products
            $products = $this->productModel->getProductsWithColumns($columns, $page, $limit, $search);

            $formattedProducts = array_map(function ($product) {
                return [
                    'id' => (int) ($product['pdt_id'] ?? 0),
                    'name' => $product['name'] ?? '',
                    'sku' => $product['sku'] ?? '',
                    'description' => $product['short_description'] ?? '',
                    'image' => $product['main_image'] ?? $this->iconBuilder->createIcon('icon-image', $product['name'] ?? 'Product', ['img'])->generate(),
                ];
            }, $products);

            $result = [
                'products' => $formattedProducts,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'hasMore' => ($page * $limit) < $total,
            ];

            // Cache the result
            $cacheType = $this->cache->getCacheType($search, $formattedProducts);
            $this->cache->setSearchResult($cacheKey, $result, $cacheType);

            return $result;
        } catch (Exception $e) {
            $this->logger->error('Product search failed', [
                'error' => $e->getMessage(),
                'page' => $page,
                'search' => $search,
            ]);

            return $this->getErrorResponse($page, $limit);
        }
    }

    public function getProductsByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $cacheKey = $this->cache->generateIdsKey($ids);

        $cached = $this->cache->getSearchResult($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $products = $this->productModel->getProductsByIds($ids);

        $formattedProducts = array_map(function ($product) {
            return [
                'id' => (int) ($product['pdt_id'] ?? $product['id'] ?? 0),
                'name' => $product['name'] ?? '',
                'sku' => $product['sku'] ?? '',
            ];
        }, $products);

        $this->cache->setSearchResult($cacheKey, $formattedProducts, 'by_ids');

        return $formattedProducts;
    }

    public function invalidateCache(?int $productId = null): void
    {
        if ($productId !== null) {
            $this->cache->invalidateByPattern('product_*');
            $this->logger->info('Product cache invalidated', ['product_id' => $productId]);
        } else {
            $this->cache->invalidateAll();
            $this->logger->info('All product cache invalidated');
        }
    }

    private function getTotalCount(string $search = '', bool $forceRefresh = false): int
    {
        $countKey = $this->cache->generateCountKey($search);

        if (!$forceRefresh) {
            $cachedCount = $this->cache->getSearchResult($countKey);
            if ($cachedCount !== null) {
                return (int) $cachedCount;
            }
        }
        $conditions = $search ? ['name', 'like', '%' . strtolower($search) . '%'] : [];
        $total = $this->productModel->count($conditions);
        $this->cache->setSearchResult($countKey, $total, 'count');

        return $total;
    }

    private function getErrorResponse(int $page, int $limit): array
    {
        return [
            'products' => [],
            'total' => 0,
            'page' => $page,
            'limit' => $limit,
            'hasMore' => false,
            'error' => 'Failed to search products',
        ];
    }
}