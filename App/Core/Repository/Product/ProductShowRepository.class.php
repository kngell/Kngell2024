<?php

declare(strict_types=1);

class ProductShowRepository extends Repository
{
    // use NestedRelationshipAliasExpanderTrait;

    protected const array COLUMN_MAPS = [
        'ProductShow' => [
            'public_id', 'pdt_id', 'sku', 'name', 'slug', 'description', 'short_description',
            'main_image', 'main_video', 'product_weight', 'product_dimension', 'stock_quantity',
            'allow_back_orders', 'is_track_stock', 'is_featured', 'is_virtual',
            'is_downloadable', 'product_visibility', 'total_sales', 'average_rating',
            'review_count', 'created_at', 'updated_at', 'deleted_at',
        ],
        'ProductStatus' => ['id', 'status_code', 'name', 'description', 'is_active'],
        'StockStatus' => ['id', 'stock_status_code', 'label', 'description', 'sort_order'],
        'Category' => ['cat_id', 'name'],
        'Brand' => ['br_id', 'name'],
        'ProductRegionalPrice' => [
            'price_id', 'region_code', 'currency_id', 'base_price', 'compare_price',
            'cost_price', 'sale_price', 'price_includes_tax', 'is_active',
        ],
        'ProductImageGallery' => ['id', 'image_url', 'alt_text', 'sort_order'],
        'ProductVariationShow' => ['id', 'name', 'variation_sku', 'price_modifier', 'stock_quantity', 'variation_status', 'created_at', 'updated_at'],
        'VariationType' => ['id', 'name'],
        'VariationAttribute' => ['id', 'attribute_name', 'attribute_value'],
    ];

    public function fetchIds(array $conditions = [], ?int $limit = null, ?int $offset = null, ?string $keyField = null): void
    {
        try {
            $qb = $this->em->createQueryBuilder();
            $select = $this->getEnrichedQueryBuilder($conditions, [$keyField ?? 'product.public_id'], $qb);

            if ($limit !== null) {
                $select->limit($limit);
            }
            if ($offset !== null) {
                $select->offset($offset);
            }

            $select->build();
        } catch (Throwable $th) {
            throw new RepositoryException('Cache lookup failed: ' . $th->getMessage(), 0, $th);
        }
    }

    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        if ($this->isArray($conditions)) {
            try {
                $qb = $this->em->createQueryBuilder();
                $select = $this->getEnrichedQueryBuilder($conditions, null, $qb);
                $select->build();
            } catch (Throwable $th) {
                throw $th;
            }
        }
    }

    public function findByID(int|string $id): void
    {
        if (empty($id)) {
            throw new RepositoryInvalidArgumentException('There is no product to find');
        }

        try {
            $qb = $this->em->createQueryBuilder();
            $find = $this->getEnrichedQueryBuilder(['product.pdt_id' => $id], null, $qb);
            $find->build();
        } catch (Throwable $th) {
            throw new RepositoryException('Failed to find Product: ' . $th->getMessage(), 0, $th);
        }
    }

    public function findAll(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        $this->findBy($conditions, $limit, $offset, $columns);
    }

    public function findBy(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        $qb = $this->em->createQueryBuilder();
        $select = $this->getEnrichedQueryBuilder($conditions, null, $qb);
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        $select->build();
        // $this->debugSql($qb);
    }

    public function count(array $conditions): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb->with('countbl')
        ->body($this->getEnrichedQueryBuilder($conditions, ['product.pdt_id'], $qb))
        ->mainquery(
            $qb->select('count(*) as totalRecord')
            ->from('countbl'),
        )->build();
        // $this->debugSql($qb);
    }

    private function getEnrichedQueryBuilder(array $conditions, ?array $columns, SqlCompositeQueryBuilderInterface $qb): SqlSelectQueryBuilderInterface
    {
        $isFullQuery = ($columns === null);
        $selectedColumns = $columns ?? self::COLUMN_MAPS['ProductShow'];

        $select = $qb->selectWithAlias($selectedColumns)
            ->distinct()
            ->from('product');

        $this->applySmartJoins($select, $conditions, $isFullQuery);
        $orderBy = [];
        if (isset($conditions['ORDER BY'])) {
            $orderBy[] = $conditions['ORDER BY'];
        }
        if (isset($conditions['order by'])) {
            $orderBy[] = $conditions['order by'];
        }
        $conditions = $this->applySqlKeywordsForSelect(
            $conditions,
            $select,
        );
        $conditions = $this->applyGlobalScopes($conditions);
        $this->applyMixedConditions($select, $conditions);

        if ($isFullQuery) {
            $select->orderBy(
                'product.pdt_id ASC',
                'product_image_gallery.sort_order ASC',
                'variation_attribute.attribute_name ASC',
            );
        } else {
            $select->orderBy('product.pdt_id ASC');
        }

        return $select;
    }

    private function applySmartJoins(SqlSelectQueryBuilderInterface $qb, array $conditions, bool $isFullQuery): void
    {
        $statusCols = $isFullQuery ? self::COLUMN_MAPS['ProductStatus'] : [];
        $stockCols = $isFullQuery ? self::COLUMN_MAPS['StockStatus'] : [];
        $catCols = $isFullQuery ? self::COLUMN_MAPS['Category'] : [];
        $brandCols = $isFullQuery ? self::COLUMN_MAPS['Brand'] : [];
        $priceCols = $isFullQuery ? self::COLUMN_MAPS['ProductRegionalPrice'] : [];
        $galleryCols = $isFullQuery ? self::COLUMN_MAPS['ProductImageGallery'] : [];
        $varCols = $isFullQuery ? self::COLUMN_MAPS['ProductVariationShow'] : [];

        // 1. Mandatory Core Joins
        $qb->leftJoin('product_status', $statusCols)->on('status_id', 'product_status.id')
           ->leftJoin('stock_status', $stockCols)->on('stock_status_id', 'stock_status.id')
           ->leftJoin('category', $catCols)->on('category_id', 'category.cat_id')
           ->leftJoin('brand', $brandCols)->on(['brand_id' => 'brand.br_id'])
           ->leftJoin('product_regional_price', $priceCols)->on(['pdt_id' => 'product_regional_price.product_id']);

        // 2. Conditional Gallery Join
        if ($isFullQuery || $this->isFilteringByTable($conditions, 'product_image_gallery')) {
            $qb->leftJoin('product_image_gallery', $galleryCols)
               ->on(['pdt_id' => 'product_image_gallery.product_id']);
        }

        // 3. Conditional Variation Joins
        if ($isFullQuery || $this->isFilteringByTable($conditions, 'product_variation')) {
            $qb->leftJoin('product_variation', $varCols)
               ->on(['pdt_id' => 'product_variation.product_id']);

            // Nested Variation Detail Joins (Only for Full View)
            if ($isFullQuery) {
                $qb->leftJoin('product_variation.variation_type', self::COLUMN_MAPS['VariationType'])
                   ->on('product_variation.variation_type_id', 'variation_type.id')
                   ->leftJoin('product_variation.variation_attribute', self::COLUMN_MAPS['VariationAttribute'])
                   ->on('product_variation.id', 'variation_attribute.variation_id');
            }
        }
    }

    private function isFilteringByTable(array $conditions, string $tableName): bool
    {
        foreach (array_keys($conditions) as $key) {
            if (str_contains((string) $key, $tableName)) {
                return true;
            }
        }
        return false;
    }
}
