<?php

declare(strict_types=1);

/**
 * @extends AbstractCollectionEntityService<ProductCollection>
 */
class ProductService extends AbstractCollectionEntityService
{
    use ProductConditionsTrait;

    private const array SECTION_LIMITS = [
        'featured' => 8,
        'new_arrivals' => 6,
        'best_sellers' => 10,
        'trending' => 8,
        'homepage' => 12,
        'sidebar' => 5,
    ];

    private ImageOptimizer $optimizer;

    public function __construct(
        private ProductCollectionModel $model,
        private ImageOptimizerFactory $imageOptimizerFactory,
        ProductCacheManagerFactory $factory,
        private RegionContextInterface $regionContext,
        private HtmlSectionPresentationService $presenter,
    ) {
        parent::__construct($factory->create());
    }

    public function getOrganizedForPage(?string $page = null): array
    {
        $responses = $this->getForPage($page);
        return $this->organizeProductsBySection($responses);
    }

    public function getBySection(string $section, array $options = []): array
    {
        $cacheKey = $this->buildSectionCacheKey($section, $options);

        return $this->cache->remember($cacheKey, function () use ($section, $options) {
            $regionCode = $this->regionContext->getRegionCode();
            $conditions = $this->buildSectionConditions($section, $options, $regionCode);
            $products = $this->fetchProducts($conditions);

            if (empty($products)) {
                return $this->getDefaultResponse();
            }

            return $this->buildResponses($products);
        });
    }

    public function getFeaturedProducts(?int $limit = null): array
    {
        return $this->getBySection('featured', ['limit' => $limit ?? self::SECTION_LIMITS['featured']]);
    }

    public function getNewArrivals(?int $limit = null): array
    {
        return $this->getBySection('new_arrivals', ['limit' => $limit ?? self::SECTION_LIMITS['new_arrivals']]);
    }

    public function getBestSellers(?int $limit = null): array
    {
        return $this->getBySection('best_sellers', ['limit' => $limit ?? self::SECTION_LIMITS['best_sellers']]);
    }

    public function getTrendingProducts(?int $limit = null): array
    {
        return $this->getBySection('trending', ['limit' => $limit ?? self::SECTION_LIMITS['trending']]);
    }

    public function getDiscountedProducts(?int $limit = null, ?string $pageTarget = null): array
    {
        /** @var ProductCardResponse[] */
        $allProducts = $this->getForPage($pageTarget);

        if (empty($allProducts)) {
            return $this->getDefaultResponse();
        }

        $discounted = array_filter($allProducts, fn ($product) => $product->isOnSale());

        usort($discounted, function ($a, $b) {
            return $b->getDiscountPercent() <=> $a->getDiscountPercent();
        });

        $limit = $limit ?? self::SECTION_LIMITS['featured'];
        $discounted = array_slice($discounted, 0, $limit);

        return !empty($discounted) ? $discounted : $this->getDefaultResponse();
    }

    public function getRelatedProducts(ProductShow $product, int $limit = 4): array
    {
        $cacheKey = "related_products_{$product->getId()}_{$limit}";

        return $this->cache->remember($cacheKey, function () use ($product, $limit) {
            $regionCode = $this->regionContext->getRegionCode();
            $conditions = $this->buildRelatedProductsConditions(
                $product->getId(),
                $product->getCategory()->getId(),
                $regionCode,
                $limit,
            );
            $products = $this->fetchProducts($conditions);
            return $this->buildResponses($products);
        });
    }

    public function getDefaultResponse(): array
    {
        return [
            $this->createResponse(
                image: $this->getDefaultImageData(),
                entity: null,
                isDefault: true,
            ),
        ];
    }

    protected function createResponse(array $image, ?Entity $entity, bool $isDefault): ProductCardResponse
    {
        return new ProductCardResponse($image, $this->presenter, $entity, $isDefault);
    }

    protected function fetchEntitiesFromDbForPage(string $page): array
    {
        $regionCode = $this->regionContext->getRegionCode();
        $conditions = $this->buildBaseProductConditions($regionCode);
        $conditions['ORDER BY'] = 'price_asc';
        $conditions['LIMIT'] = 24;
        return $this->fetchProducts($conditions);
    }

    protected function fetchEntitiesFromDbByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $regionCode = $this->regionContext->getRegionCode();
        $conditions = $this->buildBulkProductConditions($ids, $regionCode);
        return $this->fetchProducts($conditions);
    }

    protected function buildResponses(array $entities): array
    {
        $responses = [];

        foreach ($entities as $entity) {
            $responses[] = $this->createResponse(
                image: $this->buildResponsiveImageForEntity($entity),
                entity: $entity,
                isDefault: false,
            );
        }

        return $responses;
    }

    protected function buildResponsiveImageForEntity(ProductCollection $product): array
    {
        if (!isset($this->optimizer)) {
            $this->optimizer = $this->imageOptimizerFactory->create();
        }

        $imageUrl = $product->getMainImage();

        if ($imageUrl === null) {
            return $this->getDefaultImageData();
        }

        $widths = $this->getWidthsForProductCard();

        return [
            'fallback' => [
                'src' => $this->getOptimizedUrl($this->optimizer, $imageUrl, $widths['desktop']),
                'srcset' => $this->generateSrcSet($this->optimizer, $imageUrl, array_values($widths)),
                'alt' => $product->getName(),
                'width' => $widths['desktop'],
                'height' => $this->getOptimizedHeight($this->optimizer, $imageUrl, $widths['desktop']),
            ],
            'thumbnail' => [
                'src' => $this->getOptimizedUrl($this->optimizer, $imageUrl, $widths['mobile']),
                'alt' => $product->getName(),
            ],
        ];
    }

    protected function warmupIdentifier(string $identifier): int
    {
        $entities = $this->fetchEntitiesFromDbForPage($identifier);

        if (!empty($entities)) {
            $this->cache->getEntitiesForPage(
                $identifier,
                static::class,
                fn ($p) => $entities,
                fn ($ids) => $this->fetchEntitiesFromDbByIds($ids),
            );
            return count($entities);
        }

        return 0;
    }

    private function fetchProducts(array $conditions): array
    {
        $result = $this->model->all($conditions);
        return $result->isSuccess() ? $result->asClass() : [];
    }

    private function buildSectionCacheKey(string $section, array $options): string
    {
        $keyParts = ['product_section', $section];

        if (isset($options['category_id'])) {
            $keyParts[] = 'cat_' . $options['category_id'];
        }

        if (isset($options['brand_id'])) {
            $keyParts[] = 'brand_' . $options['brand_id'];
        }

        if (isset($options['limit'])) {
            $keyParts[] = 'limit_' . $options['limit'];
        }

        if (isset($options['min_price']) || isset($options['max_price'])) {
            $keyParts[] = 'price_' . ($options['min_price'] ?? '0') . '_' . ($options['max_price'] ?? '999999');
        }

        return implode('_', $keyParts);
    }

    private function organizeProductsBySection(array $responses): array
    {
        $organized = [];

        foreach ($responses as $response) {
            $product = $response->getProduct();

            if ($product instanceof ProductShow) {
                $section = 'default';

                if (!isset($organized[$section])) {
                    $organized[$section] = [];
                }

                $organized[$section][] = $response;
            }
        }

        return $organized;
    }

    private function getWidthsForProductCard(): array
    {
        if ($this->widths) {
            return $this->widths;
        }

        return [
            'mobile' => 150,
            'tablet' => 250,
            'desktop' => 350,
        ];
    }
}