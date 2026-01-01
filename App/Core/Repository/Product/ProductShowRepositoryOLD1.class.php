<?php

declare(strict_types=1);

class ProductShowRepositoryOLD1 extends Repository
{
    use NestedRelationshipAliasExpanderTrait;

    private const array COLUMN_MAPS = [
        'ProductShow' => [
            'public_id', 'pdt_id', 'sku', 'name', 'slug', 'description', 'short_description',
            'main_image', 'main_video', 'weight', 'dimensions', 'stock_quantity',
            'allow_back_orders', 'is_track_stock', 'is_featured', 'is_virtual',
            'is_downloadable', 'visibility', 'total_sales', 'average_rating',
            'review_count', 'created_at', 'updated_at', 'deleted_at',
        ],
        'ProductStatus' => [
            'id', 'status_code', 'name', 'description', 'is_active',
        ],
        'StockStatus' => [
            'id', 'stock_status_code', 'label', 'description', 'sort_order',
        ],
        'Category' => [
            'cat_id', 'name',
        ],
        'Brand' => [
            'br_id', 'name',
        ],
        'ProductRegionalPrice' => [
            'price_id', 'region_code', 'currency_id', 'base_price', 'compare_price',
            'cost_price', 'sale_price', 'price_includes_tax', 'is_active',
        ],
        'ProductImageGallery' => [
            'id', 'image_url', 'alt_text', 'sort_order',
        ],
        'ProductVariationShow' => [
            'id', 'name', 'sku', 'price_modifier', 'stock_quantity', 'status',  'created_at', 'updated_at',
        ],
        'VariationType' => [
            'id', 'name',
        ],
        'VariationAttribute' => [
            'id', 'attribute_name', 'attribute_value',
        ],
    ];

    private array $entityToTableMap = [];
    private array $relationshipPaths = [];

    public function findByIds(array $conditions = [], ?int $limit = null, ?int $offset = null): void
    {
        try {
            $queryBuilder = $this->getProductIds($conditions);
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

    public function findByID(int|string $id): void
    {
        try {
            if (empty($id)) {
                throw new RepositoryInvalidArgumentException('There is no product to find');
            }

            $qb = $this->getEnrichedQueryBuilder(['product.pdt_id' => $id]);
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

            $queryBuilder->build();
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
                ->where($conditions),
        )->select('count(*) as totalRecord')->from('countbl')->build();
    }

    public function buildAutoTableMap(string $entityClass, string $prefix = ''): array
    {
        $map = [];

        // We assume your entity has a way to report its table name
        $tableName = $entityClass::getTableName();
        $logicalKey = $prefix ?: $tableName;
        $map[$logicalKey] = $tableName;

        if (defined("$entityClass::RELATIONSHIPS")) {
            foreach ($entityClass::RELATIONSHIPS as $name => $relatedClass) {
                $newPrefix = $prefix ? "$prefix.$name" : $name;
                $map = array_merge($map, $this->buildAutoTableMap($relatedClass, $newPrefix));
            }
        }

        return $map;
    }

    private function getProductIds(array $conditions): SqlSelectQueryBuilderInterface
    {
        return $this->em->createQueryBuilder()
                   ->selectWithAlias(self::COLUMN_MAPS['ProductShow'][0])
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

    private function getEnrichedQueryBuilder2(array $conditions, ?array $columns = null): SqlSelectQueryBuilderInterface
    {
        $isFullQuery = ($columns === null);
        $selectedColumns = $columns ?? self::COLUMN_MAPS['ProductShow'];

        $qb = $this->em->createQueryBuilder()
            ->selectWithAlias($selectedColumns)
            ->distinct()
            ->from('product', 'product');

        $this->applySmartJoins($qb, $conditions, $isFullQuery);

        return $qb->where($conditions)
            ->orderBy('product.pdt_id ASC');
    }

    private function applySmartJoins(SqlSelectQueryBuilderInterface $qb, array $conditions, bool $isFullQuery): void
    {
        $statusCols = $isFullQuery ? self::COLUMN_MAPS['ProductStatus'] : [];
        $catCols = $isFullQuery ? self::COLUMN_MAPS['Category'] : [];
        $brandCols = $isFullQuery ? self::COLUMN_MAPS['Brand'] : [];
        $priceCols = $isFullQuery ? self::COLUMN_MAPS['ProductRegionalPrice'] : [];
        $galleryCols = $isFullQuery ? self::COLUMN_MAPS['ProductImageGallery'] : [];
        $variationCols = $isFullQuery ? self::COLUMN_MAPS['ProductVariationShow'] : [];

        // 1. Basic Joins
        $qb->leftJoin('product_status', $statusCols)->on('status_id', 'product_status.id')
           ->leftJoin('category', $catCols)->on('category_id', 'category.cat_id')
           ->leftJoin('brand', $brandCols)->on('brand_id', 'brand.br_id')
           ->leftJoin('product_regional_price', $priceCols)->on('pdt_id', 'product_regional_price.product_id');

        // 2. Conditional Variation Joins (Check if filtering by variation in WHERE)
        if ($isFullQuery || $this->isFilteringByTable($conditions, 'variation')) {
            $qb->leftJoin('product_variation', $variationCols)
               ->on('product.pdt_id', 'product_variation.product_id');

            // Only join attributes if it's the full data view
            if ($isFullQuery) {
                $qb->leftJoin('variation_type', self::COLUMN_MAPS['VariationType'])
                   ->on('product_variation.variation_type_id', 'variation_type.id')
                   ->leftJoin('variation_attribute', self::COLUMN_MAPS['VariationAttribute'])
                   ->on('product_variation.id', 'variation_attribute.variation_id');
            }
        }

        // 3. Conditional Gallery
        if ($isFullQuery || $this->isFilteringByTable($conditions, 'gallery')) {
            $qb->leftJoin('product_image_gallery', $galleryCols)
               ->on('product.pdt_id', 'product_image_gallery.product_id');
        }
    }

    private function isFilteringByTable(array $conditions, string $keyword): bool
    {
        foreach (array_keys($conditions) as $key) {
            if (str_contains((string) $key, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function getEnrichedQueryBuilder(array $conditions): SqlSelectQueryBuilderInterface
    {
        $qb = $this->em->createQueryBuilder()
            ->selectWithAlias(self::COLUMN_MAPS['ProductShow'])
            ->distinct()
            ->from('product');

        $this->addAllJoins($qb);

        return $qb->where($conditions)
            ->orderBy(
                'product.pdt_id ASC',
                'product_image_gallery.sort_order ASC',
                'variation_attribute.attribute_name ASC',
            );
    }

    private function addAllJoins(SqlSelectQueryBuilderInterface $qb): void
    {
        $this->addProductJoins($qb);
        $this->addVariationJoins($qb);
    }

    private function addProductJoins(SqlSelectQueryBuilderInterface $qb): void
    {
        $qb->leftJoin('product_status', self::COLUMN_MAPS['ProductStatus'])
           ->on('status_id', 'product_status.id')

           ->leftJoin('stock_status', self::COLUMN_MAPS['StockStatus'])
           ->on('stock_status_id', 'stock_status.id')

           ->leftJoin('category', self::COLUMN_MAPS['Category'])
           ->on('category_id', 'category.cat_id')

           ->leftJoin('brand', self::COLUMN_MAPS['Brand'])
           ->on(['brand_id' => 'brand.br_id'])

           ->leftJoin('product_regional_price', self::COLUMN_MAPS['ProductRegionalPrice'])
           ->on(['pdt_id' => 'product_regional_price.product_id'])

           ->leftJoin('product_image_gallery', self::COLUMN_MAPS['ProductImageGallery'])
           ->on(['pdt_id' => 'product_image_gallery.product_id'])

           ->leftJoin('product_variation', self::COLUMN_MAPS['ProductVariationShow'])
           ->on(['pdt_id' => 'product_variation.product_id']);
    }

    private function addVariationJoins(SqlSelectQueryBuilderInterface $qb): void
    {
        $qb->leftJoin('product_variation.stock_status', self::COLUMN_MAPS['StockStatus'])
           ->on('product_variation.stock_status_id', 'stock_status.id')

           ->leftJoin('product_variation.variation_type', self::COLUMN_MAPS['VariationType'])
           ->on('product_variation.variation_type_id', 'variation_type.id')

           ->leftJoin('product_variation.variation_attribute', self::COLUMN_MAPS['VariationAttribute'])
           ->on('product_variation.id', 'variation_attribute.variation_id');
    }

    private function getAllRelationshipsWithPaths(string $entityClass, string $currentPath = ''): array
    {
        $allRelationships = [];

        $reflectionClass = new ReflectionClass($entityClass);

        if (!$reflectionClass->hasConstant('RELATIONSHIPS')) {
            return $allRelationships;
        }

        $relationships = $entityClass::RELATIONSHIPS;

        foreach ($relationships as $propertyName => $relatedEntityClass) {
            // Build the full path for this relationship
            $fullPath = $currentPath ? "{$currentPath}.{$propertyName}" : $propertyName;

            // Add this relationship to the result
            $allRelationships[$fullPath] = $relatedEntityClass;

            // Recursively get nested relationships
            $nestedRelationships = $this->getAllRelationshipsWithPaths($relatedEntityClass, $fullPath);
            $allRelationships = array_merge($allRelationships, $nestedRelationships);
        }

        return $allRelationships;
    }
}