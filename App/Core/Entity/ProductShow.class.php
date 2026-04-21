<?php

declare(strict_types=1);

use Ramsey\Uuid\UuidInterface;

class ProductShow extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;
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
        'product_image_gallery' => [
            'class' => ProductImageGallery::class,
            'type' => 'one-to-many',
            'collection' => true,
        ],
        'product_variation' => [
            'class' => ProductVariationShow::class,
            'type' => 'one-to-many',
            'collection' => true,
        ],
    ];

    #[EntityFieldId(name: 'pdt_id')]
    private int $id;

    private UuidInterface $publicId;

    // Core display fields
    private string $name;
    private string $slug;
    private string $sku;
    private ?string $description = null;
    private ?string $shortDescription = null;
    private StockStatus $stockStatus;
    private int $stockQuantity = 0;

    // RelationShips
    private Category $category;
    private ProductStatus $productStatus;
    private Brand $brand;
    private ProductRegionalPrice $productRegionalPrice;

    /** @var ProductImageGallery[] */
    private array $productImageGallery = [];

    /** @var ProductVariationShow[] */
    private array $productVariationShow = [];

    // Shipping
    private ?Weight $productWeight = null;
    private ?Dimensions $productDimension = null;

    // Social/Engagement
    private float $averageRating = 0.0;
    private int $reviewCount = 0;
    private int $viewCount = 0;

    //Media
    private ?string $mainImage = null;
    private ?string $mainVideo = null;

    //Bool
    private bool $allowBackOrders = false;
    private bool $isTrackStock = true;
    private bool $isFeatured = false;
    private bool $isVirtual = false;
    private bool $isDownloadable = false;

    //Sales
    private int $totalSales = 0;
    private ?int $isOnSale = null;

    //Enum
    private ProductVisibility $productVisibility = ProductVisibility::VISIBLE;

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
     * @return ProductShow
     */
    public function setPublicId(UuidInterface $publicId): ProductShow
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
     * @return ProductShow
     */
    public function setName(string $name): ProductShow
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
     * @return ProductShow
     */
    public function setSlug(string $slug): ProductShow
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
     * @return ProductShow
     */
    public function setSku(string $sku): ProductShow
    {
        $this->sku = $sku;

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
     * @return ProductShow
     */
    public function setDescription(?string $description): ProductShow
    {
        $this->description = $description;

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
     * @return ProductShow
     */
    public function setShortDescription(?string $shortDescription): ProductShow
    {
        $this->shortDescription = $shortDescription;

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
     * @return ProductShow
     */
    public function setStockStatus(StockStatus $stockStatus): ProductShow
    {
        $this->stockStatus = $stockStatus;

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
     * @return ProductShow
     */
    public function setStockQuantity(int $stockQuantity): ProductShow
    {
        $this->stockQuantity = $stockQuantity;

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
     * @return ProductShow
     */
    public function setCategory(Category $category): ProductShow
    {
        $this->category = $category;

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
     * @return ProductShow
     */
    public function setProductStatus(ProductStatus $productStatus): ProductShow
    {
        $this->productStatus = $productStatus;

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
     * @return ProductShow
     */
    public function setBrand(Brand $brand): ProductShow
    {
        $this->brand = $brand;

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
     * @return ProductShow
     */
    public function setProductRegionalPrice(ProductRegionalPrice $productRegionalPrice): ProductShow
    {
        $this->productRegionalPrice = $productRegionalPrice;

        return $this;
    }

    /**
     * @return ProductImageGallery[]
     */
    public function getProductImageGallery(): array
    {
        return $this->productImageGallery;
    }

    /**
     * @param ProductImageGallery[] $productImageGallery
     *
     * @return ProductShow
     */
    public function setProductImageGallery(array $productImageGallery): ProductShow
    {
        $this->productImageGallery = $productImageGallery;

        return $this;
    }

    public function addProductImageGallery(ProductImageGallery $productImageGallery): void
    {
        $this->productImageGallery[] = $productImageGallery;
    }

    /**
     * @return ProductVariationShow[]
     */
    public function getProductVariationShow(): array
    {
        return $this->productVariationShow;
    }

    // /**
    //  * @param ProductVariationShow[] $productVariationShow
    //  *
    //  * @return ProductShow
    //  */
    // public function setProductVariationShow(array $productVariationShow): ProductShow
    // {
    //     $this->productVariationShow = $productVariationShow;

    //     return $this;
    // }

    public function addProductVariationShow(ProductVariationShow $productVariationShow): void
    {
        $this->productVariationShow[] = $productVariationShow;
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
     * @return ProductShow
     */
    public function setAverageRating(float $averageRating): ProductShow
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
     * @return ProductShow
     */
    public function setReviewCount(int $reviewCount): ProductShow
    {
        $this->reviewCount = $reviewCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getViewCount(): int
    {
        return $this->viewCount;
    }

    /**
     * @param int $viewCount
     *
     * @return ProductShow
     */
    public function setViewCount(int $viewCount): ProductShow
    {
        $this->viewCount = $viewCount;

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
     * @return ProductShow
     */
    public function setMainImage(?string $mainImage): ProductShow
    {
        $this->mainImage = $mainImage;

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
     * @return ProductShow
     */
    public function setMainVideo(?string $mainVideo): ProductShow
    {
        $this->mainVideo = $mainVideo;

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
     * @return ProductShow
     */
    public function setAllowBackOrders(bool $allowBackOrders): ProductShow
    {
        $this->allowBackOrders = $allowBackOrders;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsTrackStock(): bool
    {
        return $this->isTrackStock;
    }

    /**
     * @param bool $isTrackStock
     *
     * @return ProductShow
     */
    public function setIsTrackStock(bool $isTrackStock): ProductShow
    {
        $this->isTrackStock = $isTrackStock;

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
     * @return ProductShow
     */
    public function setIsFeatured(bool $isFeatured): ProductShow
    {
        $this->isFeatured = $isFeatured;

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
     * @return ProductShow
     */
    public function setIsVirtual(bool $isVirtual): ProductShow
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
     * @return ProductShow
     */
    public function setIsDownloadable(bool $isDownloadable): ProductShow
    {
        $this->isDownloadable = $isDownloadable;

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
     * @return ProductShow
     */
    public function setTotalSales(int $totalSales): ProductShow
    {
        $this->totalSales = $totalSales;

        return $this;
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
     * @return ProductShow
     */
    public function setId(int $id): ProductShow
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return ProductVisibility
     */
    public function getProductVisibility(): ProductVisibility
    {
        return $this->productVisibility;
    }

    /**
     * @param ProductVisibility $productVisibility
     *
     * @return ProductShow
     */
    public function setProductVisibility(ProductVisibility $productVisibility): ProductShow
    {
        $this->productVisibility = $productVisibility;

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
     * @return ProductShow
     */
    public function setProductWeight(?Weight $productWeight): ProductShow
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
     * @return ProductShow
     */
    public function setProductDimension(?Dimensions $productDimension): ProductShow
    {
        $this->productDimension = $productDimension;

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
     * @return ProductShow
     */
    public function setIsOnSale(?int $isOnSale): ProductShow
    {
        $this->isOnSale = $isOnSale;

        return $this;
    }
}
