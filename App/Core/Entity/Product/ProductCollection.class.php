<?php

declare(strict_types=1);

use Ramsey\Uuid\UuidInterface;

class ProductCollection extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;
    protected const array RELATIONSHIPS = [
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
        'product_regional_price' => [
            'class' => ProductRegionalPrice::class,
            'type' => 'one-to-one',
            'collection' => false,
        ],
    ];

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'pdt_id')]
    private int $id;

    #[EntityFieldId(name: 'public_id', type: FieldType::STRING)]
    private UuidInterface $publicId;

    // Core fields (only what displays in listing)
    private string $name;
    private string $slug;
    private string $sku;
    private ?string $shortDescription;
    private ?string $mainImage;
    private float $averageRating;
    private int $reviewCount;
    private int $stockQuantity;
    private bool $isFeatured;
    private ?string $description;
    private bool $allowBackOrders = true;
    private bool $isVirtual = false;
    private bool $isDownloadable = false;
    private ?Weight $productWeight = null;
    private ?Dimensions $productDimension = null;
    private int $totalSales = 0;
    private ?int $isOnSale = null;
    private ?string $mainVideo = null;
    private ?int $baseCurrencyId = null;

    // Relations (objects, not collections)
    private ProductStatus $productStatus;
    private StockStatus $stockStatus;
    private Category $category;
    private Brand $brand;
    private ProductRegionalPrice $productRegionalPrice;

    public function isInStock(): bool
    {
        return $this->stockQuantity > 0;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return ProductCollection
     */
    public function setId(int $id): ProductCollection
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return UuidInterface
     */
    public function getPublicId(): UuidInterface
    {
        return $this->publicId;
    }

    /**
     * @param UuidInterface $publicId
     *
     * @return ProductCollection
     */
    public function setPublicId(UuidInterface $publicId): ProductCollection
    {
        $this->publicId = $publicId;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return ProductCollection
     */
    public function setName(string $name): ProductCollection
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return ProductCollection
     */
    public function setSlug(string $slug): ProductCollection
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     *
     * @return ProductCollection
     */
    public function setSku(string $sku): ProductCollection
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getShortDescription(): ?string
    {
        return $this->shortDescription;
    }

    /**
     * @param null|string $shortDescription
     *
     * @return ProductCollection
     */
    public function setShortDescription(?string $shortDescription): ProductCollection
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMainImage(): ?string
    {
        return $this->mainImage;
    }

    /**
     * @param null|string $mainImage
     *
     * @return ProductCollection
     */
    public function setMainImage(?string $mainImage): ProductCollection
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    /**
     * @return float
     */
    public function getAverageRating(): float
    {
        return $this->averageRating;
    }

    /**
     * @param float $averageRating
     *
     * @return ProductCollection
     */
    public function setAverageRating(float $averageRating): ProductCollection
    {
        $this->averageRating = $averageRating;

        return $this;
    }

    /**
     * @return int
     */
    public function getReviewCount(): int
    {
        return $this->reviewCount;
    }

    /**
     * @param int $reviewCount
     *
     * @return ProductCollection
     */
    public function setReviewCount(int $reviewCount): ProductCollection
    {
        $this->reviewCount = $reviewCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getStockQuantity(): int
    {
        return $this->stockQuantity;
    }

    /**
     * @param int $stockQuantity
     *
     * @return ProductCollection
     */
    public function setStockQuantity(int $stockQuantity): ProductCollection
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsFeatured(): bool
    {
        return $this->isFeatured;
    }

    /**
     * @param bool $isFeatured
     *
     * @return ProductCollection
     */
    public function setIsFeatured(bool $isFeatured): ProductCollection
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

    /**
     * @return ProductStatus
     */
    public function getProductStatus(): ProductStatus
    {
        return $this->productStatus;
    }

    /**
     * @param ProductStatus $productStatus
     *
     * @return ProductCollection
     */
    public function setProductStatus(ProductStatus $productStatus): ProductCollection
    {
        $this->productStatus = $productStatus;

        return $this;
    }

    /**
     * @return StockStatus
     */
    public function getStockStatus(): StockStatus
    {
        return $this->stockStatus;
    }

    /**
     * @param StockStatus $stockStatus
     *
     * @return ProductCollection
     */
    public function setStockStatus(StockStatus $stockStatus): ProductCollection
    {
        $this->stockStatus = $stockStatus;

        return $this;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     *
     * @return ProductCollection
     */
    public function setCategory(Category $category): ProductCollection
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Brand
     */
    public function getBrand(): Brand
    {
        return $this->brand;
    }

    /**
     * @param Brand $brand
     *
     * @return ProductCollection
     */
    public function setBrand(Brand $brand): ProductCollection
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return ProductCollection
     */
    public function setDescription(?string $description): ProductCollection
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowBackOrders(): bool
    {
        return $this->allowBackOrders;
    }

    /**
     * @param bool $allowBackOrders
     *
     * @return ProductCollection
     */
    public function setAllowBackOrders(bool $allowBackOrders): ProductCollection
    {
        $this->allowBackOrders = $allowBackOrders;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsVirtual(): bool
    {
        return $this->isVirtual;
    }

    /**
     * @param bool $isVirtual
     *
     * @return ProductCollection
     */
    public function setIsVirtual(bool $isVirtual): ProductCollection
    {
        $this->isVirtual = $isVirtual;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDownloadable(): bool
    {
        return $this->isDownloadable;
    }

    /**
     * @param bool $isDownloadable
     *
     * @return ProductCollection
     */
    public function setIsDownloadable(bool $isDownloadable): ProductCollection
    {
        $this->isDownloadable = $isDownloadable;

        return $this;
    }

    /**
     * @return null|Weight
     */
    public function getProductWeight(): ?Weight
    {
        return $this->productWeight;
    }

    /**
     * @param null|Weight $productWeight
     *
     * @return ProductCollection
     */
    public function setProductWeight(?Weight $productWeight): ProductCollection
    {
        $this->productWeight = $productWeight;

        return $this;
    }

    /**
     * @return null|Dimensions
     */
    public function getProductDimension(): ?Dimensions
    {
        return $this->productDimension;
    }

    /**
     * @param null|Dimensions $productDimension
     *
     * @return ProductCollection
     */
    public function setProductDimension(?Dimensions $productDimension): ProductCollection
    {
        $this->productDimension = $productDimension;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalSales(): int
    {
        return $this->totalSales;
    }

    /**
     * @param int $totalSales
     *
     * @return ProductCollection
     */
    public function setTotalSales(int $totalSales): ProductCollection
    {
        $this->totalSales = $totalSales;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getIsOnSale(): ?int
    {
        return $this->isOnSale;
    }

    /**
     * @param null|int $isOnSale
     *
     * @return ProductCollection
     */
    public function setIsOnSale(?int $isOnSale): ProductCollection
    {
        $this->isOnSale = $isOnSale;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMainVideo(): ?string
    {
        return $this->mainVideo;
    }

    /**
     * @param null|string $mainVideo
     *
     * @return ProductCollection
     */
    public function setMainVideo(?string $mainVideo): ProductCollection
    {
        $this->mainVideo = $mainVideo;

        return $this;
    }

    /**
     * @return ProductRegionalPrice
     */
    public function getProductRegionalPrice(): ProductRegionalPrice
    {
        return $this->productRegionalPrice;
    }

    /**
     * @param ProductRegionalPrice $productRegionalPrice
     *
     * @return ProductCollection
     */
    public function setProductRegionalPrice(ProductRegionalPrice $productRegionalPrice): ProductCollection
    {
        $this->productRegionalPrice = $productRegionalPrice;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getBaseCurrencyId(): ?int
    {
        return $this->baseCurrencyId;
    }

    /**
     * @param null|int $baseCurrencyId
     *
     * @return ProductCollection
     */
    public function setBaseCurrencyId(?int $baseCurrencyId): ProductCollection
    {
        $this->baseCurrencyId = $baseCurrencyId;

        return $this;
    }
}