<?php

declare(strict_types=1);

/**
 * @extends AbstractCollectionEntityService<ProductShow>
 */
class ProductCardService extends AbstractCollectionEntityService
{
    private const array SECTION_LIMITS = [
        'featured' => 8,
        'new_arrivals' => 6,
        'best_sellers' => 10,
        'trending' => 8,
        'homepage' => 12,
        'sidebar' => 5,
    ];

    public function __construct(
        private ProductShowModel $model,
        ImageOptimizerFactory $imageOptimizerFactory,
        ProductCardCacheManagerFactory $factory,
    ) {
        parent::__construct($imageOptimizerFactory, $factory->create());
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
            $conditions = $this->buildConditionsForSection($section, $options);
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

    public function getRelatedProducts(ProductShow $product, int $limit = 4): array
    {
        $cacheKey = "related_products_{$product->getId()}_{$limit}";

        return $this->cache->remember($cacheKey, function () use ($product, $limit) {
            $conditions = [
                'category.id' => $product->getCategory()->getId(),
                'id !=' => $product->getId(),
                'is_active' => true,
                'ORDER BY' => 'RAND()',
                'LIMIT' => $limit,
            ];

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
        return new ProductCardResponse($image, $entity, $isDefault);
    }

    protected function fetchEntitiesFromDbForPage(string $page): array
    {
        $conditions = $this->buildConditionsForSection($page, ['limit' => self::SECTION_LIMITS[$page] ?? 12]);
        return $this->fetchProducts($conditions);
    }

    protected function fetchEntitiesFromDbByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $result = $this->model->all(['id', 'in', $ids]);
        return $result->isSuccess() ? $result->asClass() : [];
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

    protected function buildResponsiveImageForEntity(ProductShow $product): array
    {
        $optimizer = $this->imageOptimizerFactory->create();
        $imageUrl = $product->getMainImage();

        if ($imageUrl === null) {
            return $this->getDefaultImageData();
        }

        $widths = $this->getWidthsForProductCard();

        return [
            'fallback' => [
                'src' => $this->getOptimizedUrl($optimizer, $imageUrl, $widths['desktop']),
                'srcset' => $this->generateSrcSet($optimizer, $imageUrl, array_values($widths)),
                'alt' => $product->getName(),
                'width' => $widths['desktop'],
                'height' => $this->getOptimizedHeight($optimizer, $imageUrl, $widths['desktop']),
            ],
            'thumbnail' => [
                'src' => $this->getOptimizedUrl($optimizer, $imageUrl, $widths['mobile']),
                'alt' => $product->getName(),
            ],
        ];
    }

    protected function getDefaultImageData(): array
    {
        return [
            'fallback' => [
                'src' => '/assets/images/default-product.jpg',
                'srcset' => '/assets/images/default-product.jpg 400w',
                'alt' => 'Product image',
                'width' => 400,
                'height' => 300,
            ],
        ];
    }

    // ================= PRIVATE HELPER METHODS ==============

    private function buildConditionsForSection(string $section, array $options = []): array
    {
        $limit = $options['limit'] ?? self::SECTION_LIMITS[$section] ?? 12;

        $conditions = [
            'is_active' => true,
            'ORDER BY' => 'created_at DESC',
            'limit' => $limit,
        ];

        switch ($section) {
            case 'featured':
                $conditions['is_featured'] = true;
                // $conditions['has_main_image'] = true;
                break;
            case 'new_arrivals':
                $conditions['created_at >='] = date('Y-m-d', strtotime('-30 days'));
                // $conditions['has_main_image'] = true;
                break;
            case 'best_sellers':
                $conditions['ORDER BY'] = 'total_sales DESC, created_at DESC';
                // $conditions['has_main_image'] = true;
                break;
            case 'trending':
                $conditions['ORDER BY'] = 'view_count DESC, created_at DESC';
                // $conditions['has_main_image'] = true;
                break;
            case 'sidebar':
                $conditions['show_in_sidebar'] = true;
                $conditions['limit'] = $limit;
                break;
            case 'homepage':
            default:
                // $conditions['show_on_homepage'] = true;
                // $conditions['has_main_image'] = true;
                break;
        }

        // Apply category filter if provided
        if (isset($options['category_id'])) {
            $conditions['category.id'] = $options['category_id'];
        }

        // Apply brand filter if provided
        if (isset($options['brand_id'])) {
            $conditions['brand.id'] = $options['brand_id'];
        }

        // Apply price range if provided
        if (isset($options['min_price']) || isset($options['max_price'])) {
            $priceConditions = [];
            if (isset($options['min_price'])) {
                $priceConditions['product_regional_price.price >='] = $options['min_price'];
            }
            if (isset($options['max_price'])) {
                $priceConditions['product_regional_price.price <='] = $options['max_price'];
            }
            $conditions = array_merge($conditions, $priceConditions);
        }

        return $conditions;
    }

    private function fetchProducts(array $conditions): array
    {
        $result = $this->model->all($conditions);
        return $result->isSuccess() ? $result->asClass() : [];
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
                $section = 'default'; //$product->getDisplaySection() ?? 'default';

                if (!isset($organized[$section])) {
                    $organized[$section] = [];
                }

                $organized[$section][] = $response;
            }
        }

        return $organized;
    }
}
