<?php

declare(strict_types=1);

use Brick\Money\Money;
use Ramsey\Uuid\UuidInterface;

/**
 * READ-ONLY ENTITY - Admin product management table
 * Shows aggregated data with counts.
 */
class ProductAdminListing extends Entity
{
    protected const array RELATIONSHIPS = [
        // Only 1:1 relationships
        'product_status' => [
            'class' => ProductStatus::class,
            'type' => 'one-to-one',
            'collection' => false,
        ],
        'stock_status' => [
            'class' => StockStatus::class,
            'type' => 'one-to-one',
            'collection' => false,
        ],
        'category' => [
            'class' => Category::class,
            'type' => 'one-to-one',
            'collection' => false,
        ],
        'brand' => [
            'class' => Brand::class,
            'type' => 'one-to-one',
            'collection' => false,
        ],
        'regional_price' => [
            'class' => ProductRegionalPrice::class,
            'type' => 'one-to-one',
            'collection' => false,
        ],
    ];

    #[EntityFieldId(name: 'pdt_id')]
    private int $id;

    #[EntityFieldId(name: 'public_id', type: FieldType::STRING)]
    private UuidInterface $publicId;

    // Basic fields
    private string $name;
    private string $sku;
    private ?string $mainImage;
    private int $stockQuantity;
    private bool $isActive;
    private bool $isFeatured;

    // Relations
    private ProductStatus $productStatus;
    private StockStatus $stockStatus;
    private Category $category;
    private Brand $brand;
    private ProductRegionalPrice $regionalPrice;

    // Aggregated counts (populated via subqueries, not joins)
    private int $variationsCount = 0;
    private int $imagesCount = 0;
    private ?Money $minPrice = null;
    private ?Money $maxPrice = null;
}