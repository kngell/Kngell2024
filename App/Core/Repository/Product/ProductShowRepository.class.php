<?php

declare(strict_types=1);

class ProductShowRepository extends Repository
{
    private const array PRODUCT = [
        'pdt_id', 'public_id', 'sku', 'name', 'slug', 'description', 'short_description', 'main_image', 'main_video', 'weight', 'dimensions', 'stock_quantity', 'allow_back_orders', 'is_track_stock', 'is_featured', 'is_virtual', 'is_downloadable', 'visibility', 'tags', 'total_sales', 'average_rating', 'review_count',
    ];
    private const array STOCK_STATUS = [
        'id', 'stock_status_code', 'label', 'description', 'sort_order',
    ];
    private const array CATEGORY = [
        'name',
    ];
    private const array BRAND = [
        'name',
    ];

    private const array PRODUCT_REGIONAL_PRICE = [
        'region_code', 'base_price', 'compare_price', 'cost_price', 'sale_price', 'price_includes_tax', 'is_active',
    ];
    private const array PRODUCT_IMAGE_GALLERY = [
        'id', 'image_url', 'alt_text', 'sort_order',
    ];
    private const array PRODUCT_VARIATION = [
        'name', 'sku', 'price_modifier', 'stock_quantity',
    ];
    private const array VARIATION_TYPE = [
        'name',
    ];
    private const array STOCK_STATUS_VARIATION = [
        'stock_status_code', 'label',
    ];
    private const array VARIATION_ATTRIBUTE = [
        'attribute_name', 'attribute_value',
    ];

    public function findByID(int|string $id): void
    {
        try {
            if (empty($id)) {
                throw new RepositoryInvalidArgumentException('There is no product to find');
            }

            $qb = $this->em->createQueryBuilder()
                ->selectAsAlias(self::PRODUCT)
                ->from('product')
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
                ->where('pdt_id', $id)
                ->andWhere('is_active', true)
                ->andWhere('status', 'active')
                ->orderBy(
                    'product_image_gallery.sort_order ASC',
                    'variation_attribute.attribute_name ASC',
                );

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
}