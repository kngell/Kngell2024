<?php

declare(strict_types=1);

trait ProductConditionsTrait
{
    private function buildBaseProductConditions(?string $regionCode = null, ?string $statusCode = 'active'): array
    {
        $conditions = [
            'is_active' => true,
            'deleted_at is null',
            'product_status.status_code' => $statusCode,
        ];

        if ($regionCode !== null) {
            $conditions['product_regional_price.region_code'] = $regionCode;
        }

        return $conditions;
    }

    private function buildSectionConditions(string $section, array $options = [], ?string $regionCode = null): array
    {
        $conditions = $this->buildBaseProductConditions($regionCode);
        $limit = $options['limit'] ?? 12;

        switch ($section) {
            case 'featured':
                $conditions['is_featured'] = true;
                break;
            case 'new_arrivals':
                $conditions['created_at >='] = date('Y-m-d', strtotime('-30 days'));
                break;
            case 'best_sellers':
                $conditions['ORDER BY'] = 'total_sales DESC, created_at DESC';
                break;
            case 'discount':
                $conditions['is_on_sale'] = true;
                $conditions['ORDER BY'] = 'view_count DESC';
                break;
            case 'sidebar':
                $conditions['show_in_sidebar'] = true;
                break;
            default:
                $conditions['ORDER BY'] = 'created_at DESC';
                break;
        }

        if (isset($options['category_id'])) {
            $conditions['category.cat_id'] = $options['category_id'];
        }

        if (isset($options['brand_id'])) {
            $conditions['brand.br_id'] = $options['brand_id'];
        }

        if (isset($options['min_price'])) {
            $conditions['product_regional_price.base_price >='] = $options['min_price'];
        }
        if (isset($options['max_price'])) {
            $conditions['product_regional_price.base_price <='] = $options['max_price'];
        }

        $conditions['LIMIT'] = $limit;

        return $conditions;
    }

    private function buildSingleProductConditions(int $productId, ?string $regionCode = null): array
    {
        $conditions = $this->buildBaseProductConditions($regionCode);
        $conditions['product.pdt_id'] = $productId;
        $conditions['LIMIT'] = 1;

        return $conditions;
    }

    private function buildBulkProductConditions(array $productIds, ?string $regionCode = null): array
    {
        $conditions = $this->buildBaseProductConditions($regionCode);
        $conditions['product.pdt_id IN'] = $productIds;

        return $conditions;
    }

    private function buildRelatedProductsConditions(
        int $productId,
        int $categoryId,
        ?string $regionCode = null,
        int $limit = 4,
    ): array {
        $conditions = $this->buildBaseProductConditions($regionCode);
        $conditions['category.cat_id'] = $categoryId;
        $conditions['product.pdt_id !='] = $productId;
        $conditions['ORDER BY'] = 'RAND()';
        $conditions['LIMIT'] = $limit;

        return $conditions;
    }
}