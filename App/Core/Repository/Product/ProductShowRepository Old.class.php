<?php

declare(strict_types=1);
class ProductShowRepositoryOLD extends Repository
{
    use NestedRelationshipAliasExpanderTrait;
    private const array PRODUCT = [
        'public_id', 'pdt_id', 'sku', 'name', 'slug', 'description', 'short_description', 'main_image', 'main_video', 'weight', 'dimensions', 'stock_quantity', 'allow_back_orders', 'is_track_stock', 'is_featured', 'is_virtual', 'is_downloadable', 'visibility', 'total_sales', 'average_rating', 'review_count', 'created_at', 'updated_at', 'deleted_at',
    ];
    private const array PRODUCT_STATUS = [
        'id', 'status_code', 'name', 'description', 'is_active',
    ];
    private const array STOCK_STATUS = [
        'id', 'stock_status_code', 'label', 'description', 'sort_order',
    ];
    private const array CATEGORY = [
        'cat_id', 'name',
    ];
    private const array BRAND = [
        'br_id', 'name',
    ];

    private const array PRODUCT_REGIONAL_PRICE = [
        'price_id', 'region_code', 'currency_id', 'base_price', 'compare_price', 'cost_price', 'sale_price', 'price_includes_tax', 'is_active',
    ];
    private const array PRODUCT_IMAGE_GALLERY = [
        'id', 'image_url', 'alt_text', 'sort_order',
    ];
    private const array PRODUCT_VARIATION = [
        'id', 'name', 'sku', 'price_modifier', 'stock_quantity',
    ];
    private const array VARIATION_TYPE = [
        'id', 'name',
    ];
    private const array STOCK_STATUS_VARIATION = [
        'id', 'stock_status_code', 'label',
    ];
    private const array VARIATION_ATTRIBUTE = [
        'id', 'attribute_name', 'attribute_value',
    ];

    public function findByID(int|string $id): void
    {
        try {
            if (empty($id)) {
                throw new RepositoryInvalidArgumentException('There is no product to find');
            }
            $qb = $this->getEnrichedQueryBuilder(['pdt_id', $id]);
            $qb->build();
        } catch (RepositoryInvalidArgumentException $e) {
            throw $e;
        } catch (Throwable $th) {
            throw new RepositoryException(
                'Failed to find Product for display : ' . $th->getMessage(),
                $th->getCode(),
                $th,
            );
        }
    }

    public function findOneBy(array $conditions = []): void
    {
        $qb = $this->getEnrichedQueryBuilder($conditions);
        $qb->build();
    }

    public function findAll(array $conditions = []): void
    {
        try {
            $qb = $this->getEnrichedQueryBuilder($conditions);

            $qb->build();
        } catch (RepositoryInvalidArgumentException $e) {
            throw $e;
        } catch (Throwable $th) {
            throw new RepositoryException(
                'Failed to find Product for display : ' . $th->getMessage(),
                $th->getCode(),
                $th,
            );
        }
    }

    public function findBy(array $conditions = [], ?int $limit = null, ?int $offset = null): void
    {
        try {
            $queryBuilder = $this->getEnrichedQueryBuilder($conditions);

            if ($limit !== null) {
                $queryBuilder->limit($limit);
            }
            if ($offset !== null) {
                $queryBuilder->offset($offset);
            }

            // Build and check what we get
            $builtQuery = $queryBuilder->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    public function count(array $conditions): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->with(
            'countbl',
            $qb->selectWithAlias('pdt_id')
                ->distinct()
                ->from('product')
                ->leftJoin('product_status')
                ->on('status_id', 'product_status.id')
                ->leftJoin('stock_status')
                ->on('stock_status_id', 'stock_status.id')
                ->leftJoin('category')
                ->on('category_id', 'category.cat_id')
                ->leftJoin('brand')
                ->on(['brand_id' => 'brand.br_id'])
                ->leftJoin('product_regional_price')
                ->on(['pdt_id' => 'product_regional_price.product_id'])
                ->leftJoin('product_image_gallery')
                ->on(['pdt_id' => 'product_image_gallery.product_id'])
                ->leftJoin('product_variation')
                ->on(['pdt_id' => 'product_variation.product_id'])
                ->leftJoin('stock_status')
                ->on('product_variation.stock_status_id', 'stock_status.id')
                ->leftJoin('variation_type')
                ->on('product_variation.variation_type_id', 'variation_type.id')
                ->leftJoin('variation_attribute')
                ->on('product_variation.id', 'variation_attribute.variation_id')
                ->where($conditions),
        )->select('count(*) as totalRecord')->from('countbl')
        ->build();
    }

    public function findByIds(array $conditions = [], ?int $limit = null, ?int $offset = null, ?string $keyField = null): void
    {
        try {
            $queryBuilder = $this->getProductIds($conditions);

            // Add pagination if provided
            if ($limit !== null) {
                $queryBuilder->limit($limit);
            }
            if ($offset !== null) {
                $queryBuilder->offset($offset);
            }

            $queryBuilder->build();
        } catch (Throwable $th) {
            throw $th;
        }
    }

    private function getProductIds(array $conditions): SqlSelectQueryBuilderInterface
    {
        return $this->em->createQueryBuilder()
                   ->selectWithAlias(self::PRODUCT[0])
                   ->from('product')
                   ->leftJoin('product_status')
                   ->on('status_id', 'product_status.id')
                   ->leftJoin('stock_status')
                   ->on('stock_status_id', 'stock_status.id')
                   ->leftJoin('category')
                   ->on('category_id', 'category.cat_id')
                   ->leftJoin('brand')
                   ->on(['brand_id' => 'brand.br_id'])
                   ->leftJoin('product_regional_price')
                   ->on(['pdt_id' => 'product_regional_price.product_id'])
                   ->leftJoin('product_image_gallery')
                   ->on(['pdt_id' => 'product_image_gallery.product_id'])
                   ->leftJoin('product_variation')
                   ->on(['pdt_id' => 'product_variation.product_id'])
                   ->leftJoin('stock_status')
                   ->on('product_variation.stock_status_id', 'stock_status.id')
                   ->leftJoin('variation_type')
                   ->on('product_variation.variation_type_id', 'variation_type.id')
                   ->leftJoin('variation_attribute')
                   ->on('product_variation.id', 'variation_attribute.variation_id')
                   ->where($conditions)
                   ->orderBy(
                       'product_image_gallery.sort_order ASC',
                       'variation_attribute.attribute_name ASC',
                   );
    }

    private function getEnrichedQueryBuilder(array $conditions): SqlSelectQueryBuilderInterface
    {
        return $this->em->createQueryBuilder()
                ->selectWithAlias(self::PRODUCT)
                ->distinct()
                ->from('product')
                ->leftJoin('product_status', self::PRODUCT_STATUS)
                ->on('status_id', 'product_status.id')
                ->leftJoin('stock_status', self::STOCK_STATUS)
                ->on('stock_status_id', 'stock_status.id')
                ->leftJoin('category', self::CATEGORY)
                ->on('category_id', 'category.cat_id')
                ->leftJoin('brand', self::BRAND)
                ->on(['brand_id' => 'brand.br_id'])
                ->leftJoin('product_regional_price', self::PRODUCT_REGIONAL_PRICE)
                ->on(['pdt_id' => 'product_regional_price.product_id'])
                ->leftJoin('product_image_gallery', self::PRODUCT_IMAGE_GALLERY)
                ->on(['pdt_id' => 'product_image_gallery.product_id'])
                ->leftJoin('product_variation', self::PRODUCT_VARIATION)
                ->on(['pdt_id' => 'product_variation.product_id'])
                ->leftJoin('stock_status', self::STOCK_STATUS_VARIATION)
                ->on('product_variation.stock_status_id', 'stock_status.id')
                ->leftJoin('variation_type', self::VARIATION_TYPE)
                ->on('product_variation.variation_type_id', 'variation_type.id')
                ->leftJoin('variation_attribute', self::VARIATION_ATTRIBUTE)
                ->on('product_variation.id', 'variation_attribute.variation_id')
                ->where($conditions)
                ->orderBy(
                    'pdt_id ASC',
                    'product_image_gallery.sort_order ASC',
                    'variation_attribute.attribute_name ASC',
                );
    }
}