<?php

declare(strict_types=1);

class ProductPriceService extends AbstractSingleEntityService
{
    use ProductConditionsTrait;

    private static array $requestCache = [];
    private static array $priceCache = [];

    public function __construct(
        private readonly ProductCollectionModel $model,
        private readonly ProductCacheManagerFactory $cacheFactory,
        private readonly HtmlSectionPresentationService $presenter,
        private readonly ImageOptimizerFactory $imageOptimizerFactory,
        private readonly RegionContextInterface $regionContext,
    ) {
        parent::__construct($cacheFactory->create());
    }

    /**
     * Get price for a single product with request-level caching.
     */
    public function getPriceForProduct(int $productId): ProductPriceResponse
    {
        // Check request cache first
        $cacheKey = 'price_' . $productId;
        if (isset(self::$priceCache[$cacheKey])) {
            return self::$priceCache[$cacheKey];
        }

        $page = 'product_price_' . $productId;
        $response = $this->getForPage($page);

        if ($response instanceof ProductPriceResponse) {
            // Store in request cache
            self::$priceCache[$cacheKey] = $response;
            return $response;
        }

        $defaultResponse = $this->getDefaultResponse();
        self::$priceCache[$cacheKey] = $defaultResponse;
        return $defaultResponse;
    }

    /**
     * Batch load prices for multiple products with request-level caching.
     */
    public function getPricesForProducts(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $productIds = array_unique($productIds);
        sort($productIds);

        // Build a cache key for this batch request
        $batchKey = 'batch_' . md5(implode('|', $productIds));

        // Check request cache first
        if (isset(self::$requestCache[$batchKey])) {
            return self::$requestCache[$batchKey];
        }

        $results = [];
        $missingIds = [];

        // Step 1: Get from request cache or parent
        foreach ($productIds as $id) {
            // Check if already loaded in this request
            $priceKey = 'price_' . $id;
            if (isset(self::$priceCache[$priceKey])) {
                $results[$id] = self::$priceCache[$priceKey];
                continue;
            }

            // Try parent's cache system
            $page = 'product_price_' . $id;
            $response = $this->getForPage($page);

            if ($response instanceof ProductPriceResponse && !$response->isDefault()) {
                self::$priceCache[$priceKey] = $response;
                $results[$id] = $response;
            } else {
                $missingIds[] = $id;
            }
        }

        // Step 2: Batch load missing products
        if (!empty($missingIds)) {
            $dbStart = microtime(true);
            $products = $this->model->getProductsByIds($missingIds);
            $dbTime = (microtime(true) - $dbStart) * 1000;

            $this->logger?->debug('[ProductPriceService] Batch loaded ' . count($missingIds) . ' products in ' . round($dbTime, 2) . 'ms');

            foreach ($products as $product) {
                // Build response using parent's createResponse method
                $response = $this->createResponse(
                    image: $this->buildResponsiveImage($product),
                    entity: $product,
                    isDefault: false,
                );

                // Cache the entity for future requests via parent
                $page = 'product_price_' . $product->getId();
                $this->cache->getEntityForPage(
                    $page,
                    static::class,
                    fn ($p) => $product,
                    fn ($id) => $product,
                );

                // Store in request cache
                $priceKey = 'price_' . $product->getId();
                self::$priceCache[$priceKey] = $response;
                $results[$product->getId()] = $response;
            }
        }

        // Store the entire batch in request cache
        self::$requestCache[$batchKey] = $results;

        return $results;
    }

    public function warmupPrices(array $productIds): int
    {
        $warmed = 0;
        foreach ($productIds as $productId) {
            $page = 'product_price_' . $productId;
            $warmed += $this->warmupIdentifier($page);
        }
        return $warmed;
    }

    #[Override]
    public function getDefaultResponse(): ProductPriceResponse
    {
        return new ProductPriceResponse(
            image: $this->getDefaultImageData(),
            product: null,
            isDefault: true,
            presenter: $this->presenter,
        );
    }

    #[Override]
    protected function fetchEntityFromDb(string $page): ?Entity
    {
        if (str_starts_with($page, 'product_price_')) {
            $productId = (int) substr($page, 14);
            $regionCode = $this->regionContext->getRegionCode();
            $conditions = $this->buildSingleProductConditions($productId, $regionCode);

            $result = $this->model->one($conditions);
            if ($result->exists()) {
                return $result->asClass(ProductCollection::class);
            }
        }

        return null;
    }

    #[Override]
    protected function fetchEntityByIdFromDb(string $id): ?Entity
    {
        $regionCode = $this->regionContext->getRegionCode();
        $conditions = $this->buildSingleProductConditions((int) $id, $regionCode);

        $result = $this->model->one($conditions);
        if ($result->exists()) {
            return $result->asClass(ProductCollection::class);
        }

        return null;
    }

    #[Override]
    protected function buildResponsiveImage(Entity $entity): array
    {
        if (!$entity instanceof ProductCollection) {
            return $this->getDefaultImageData();
        }

        $imageUrl = $entity->getMainImage();
        if ($imageUrl === null) {
            return $this->getDefaultImageData();
        }

        $optimizer = $this->imageOptimizerFactory->create();
        $widths = $this->getWidthsForProduct();

        return [
            'fallback' => [
                'src' => $this->getOptimizedUrl($optimizer, $imageUrl, $widths['desktop']),
                'srcset' => $this->generateSrcSet($optimizer, $imageUrl, array_values($widths)),
                'alt' => $entity->getName(),
                'width' => $widths['desktop'],
                'height' => $this->getOptimizedHeight($optimizer, $imageUrl, $widths['desktop']),
            ],
            'thumbnail' => [
                'src' => $this->getOptimizedUrl($optimizer, $imageUrl, $widths['mobile']),
                'alt' => $entity->getName(),
            ],
        ];
    }

    #[Override]
    protected function createResponse(array $image, ?Entity $entity, bool $isDefault): EntityResponseInterface
    {
        return new ProductPriceResponse(
            image: $image,
            product: $entity instanceof ProductCollection ? $entity : null,
            isDefault: $isDefault,
            presenter: $this->presenter,
        );
    }

    #[Override]
    protected function warmupIdentifier(string $identifier): int
    {
        $entity = $this->fetchEntityFromDb($identifier);
        if ($entity) {
            $this->cache->getEntityForPage(
                $identifier,
                static::class,
                fn ($p) => $entity,
                fn ($id) => $this->fetchEntityByIdFromDb($id),
            );
            return 1;
        }
        return 0;
    }

    private function getWidthsForProduct(): array
    {
        return [
            'mobile' => 150,
            'tablet' => 250,
            'desktop' => 350,
        ];
    }

    /**
     * Clear request cache (useful for testing).
     */
    public static function clearRequestCache(): void
    {
        self::$requestCache = [];
        self::$priceCache = [];
    }
}