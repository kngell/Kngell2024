<?php

declare(strict_types=1);

class ProductCollectionRepository extends Repository
{
    protected const array COLUMN_MAPS = [
        'product' => ['pdt_id', 'public_id', 'name', 'slug', 'sku', 'short_description', 'description', 'main_image', 'average_rating', 'review_count', 'stock_quantity', 'allow_back_orders', 'is_virtual', 'is_downloadable', 'product_weight', 'product_dimension', 'total_sales', 'is_on_sale', 'main_video', 'is_featured', 'base_currency_id', 'created_at', 'updated_at'],
        'product_status' => ['id', 'status_code', 'name', 'is_active'],
        'stock_status' => ['id', 'stock_status_code', 'label', 'description', 'sort_order'], // Fixed casing
        'category' => ['cat_id', 'name'],                                                // Fixed casing
        'brand' => ['br_id', 'name'],                                                 // Fixed casing
        'product_regional_price' => ['price_id', 'region_code', 'currency_id', 'base_price', 'compare_price', 'cost_price', 'sale_price', 'price_includes_tax', 'is_active'], // Fixed casing
    ];

    #[Override]
    public function findAll(array $conditions = [], ?int $limit = null, ?int $offset = null, array $columns = []): void
    {
        $this->findForCollection($conditions, $limit ?? 24, $offset ?? 0);
    }

    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        $this->findForCollection($conditions, $limit ?? 1, $offet ?? 0);
    }

    #[Override]
    public function count(array $conditions): void
    {
        $this->countForCollection($conditions);
    }

    private function findForCollection(
        array $conditions = [],
        int $limit = 24,
        int $offset = 0,
    ): void {
        $qb = $this->em->createQueryBuilder();

        $regionCode = $conditions['region_code'] ?? null;
        unset($conditions['region_code']);

        // $statusCode = $conditions['status_code'] ?? 'published';
        // unset($conditions['status_code']);

        // Extract sort parameter
        $sort = $conditions['ORDER BY'] ?? 'newest';
        unset($conditions['ORDER BY']);

        // Build the select with all column maps
        $select = $qb->selectWithAlias($this->getAllColumns())
             ->from('product')
             ->innerJoin('product_status')
                 ->on('status_id', 'product_status.id')
             ->leftJoin('stock_status')
                 ->on('stock_status_id', 'stock_status.id')
             ->leftJoin('category')
                 ->on('category_id', 'category.cat_id')
             ->leftJoin('brand')
                 ->on('brand_id', 'brand.br_id')
             ->innerJoin('product_regional_price')
                 ->on('pdt_id', 'product_regional_price.product_id');

        if ($regionCode !== null) {
            $select->onValue('product_regional_price.region_code', $regionCode);
        }

        $select->onValue('product_regional_price.is_active', true);

        if (!empty($conditions)) {
            $this->applyMixedConditions($select, $conditions);
        }
        // Apply sorting
        switch ($sort) {
            case 'price_asc':
                $select->orderBy('product_regional_price.base_price ASC');
                break;
            case 'price_desc':
                $select->orderBy('product_regional_price.base_price DESC');
                break;
            case 'name_asc':
                $select->orderBy('name ASC');
                break;
            case 'name_desc':
                $select->orderBy('name DESC');
                break;
            case 'newest':
            default:
                $select->orderBy('created_at DESC');
                break;
        }

        // Apply pagination
        if ($limit > 0) {
            $select->limit($limit);
        }
        if ($offset > 0) {
            $select->offset($offset);
        }

        // Execute query
        $select->build();
        // $this->debugSql($qb);
    }

    private function countForCollection(array $conditions = []): void
    {
        $qb = $this->em->createQueryBuilder();

        // Extract status_code before applying conditions
        $statusCode = $conditions['status_code'] ?? 'published';
        unset($conditions['status_code']);

        $select = $qb->select('COUNT(DISTINCT product.pdt_id) as total')
            ->from('product')
            ->leftJoin('product_status')
                ->on('status_id', 'product_status.id')
            ->where('is_active', true)
            ->andWhere('deleted_at IS NULL')
            ->andWhere('product_status.status_code', $statusCode);

        // Apply remaining conditions (category_id, etc.)
        if (!empty($conditions)) {
            $this->applyMixedConditions($select, $conditions);
        }

        $select->build();
        // $this->debugSql($qb); // Uncomment when debugging
    }
}