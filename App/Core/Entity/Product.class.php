<?php

declare(strict_types=1);

use Ramsey\Uuid\UuidInterface;

class Product extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        prefix: '#',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'pdt_id', type: FieldType::INT)]
    private int $id;

    #[DisplayFormat(
        style: 'uuid',
        prefix: 'ID: ',
        suffix: ' (UUID)',
    )]
    #[EntityFieldId(name: 'public_id', type: FieldType::STRING)]
    private UuidInterface $publicId;

    // 🔒 Required core fields
    private string $sku;
    private string $name;
    private string $slug;
    private int $statusId;
    private int $stockStatusId;
    private bool $isActive = true;
    private bool $isFeatured = false;
    private bool $isVirtual = false;
    private bool $isDownloadable = false;
    private ProductVisibility $productVisibility = ProductVisibility::VISIBLE;

    // 🟡 Optional fields
    private ?string $description = null;
    private ?string $shortDescription = null;
    private ?int $categoryId = null;
    private ?int $brandId = null;
    private int $baseCurrencyId = 1;
    private ?int $taxClassId;
    private bool $priceIncludesTax = false;

    // 📦 Inventory Management
    private bool $isTrackStock = true;
    private int $stockQuantity = 0;
    private bool $allowBackOrders = false;
    private int $lowStockThreshold = 5;
    private int $minOrderQuantity = 1;
    private int $maxOrderQuantity = 0;

    // ⚖️ Physical Properties
    private ?Weight $productWeight = null;
    private ?Dimensions $productDimension = null;

    // 🖼️ Media
    private ?string $mainImage = null;
    private ?string $mainVideo = null;

    // 📦 Shipping
    private ?int $shippingClassId = null;
    private bool $requiresShipping = true;

    // 📊 Sales & Performance
    private int $totalSales = 0;
    private float $averageRating = 0.0;
    private int $reviewCount = 0;

    // 👤 Audit
    private ?int $createdBy = null;
    private ?int $updatedBy = null;
    private ?int $isOnSale = null;

    #[NotPersisted]
    /** @var ProductRegionalPrice[] */
    private array $regionalPrices = [];

    /**
     * Helper method to get price for specific region.
     */
    public function getPriceForRegion(string $regionCode): ?ProductRegionalPrice
    {
        /** @var ProductRegionalPrice|null $regionalPrice */
        foreach ($this->regionalPrices as $regionalPrice) {
            if ($regionalPrice->getRegionCode() === $regionCode) {
                return $regionalPrice;
            }
        }
        return null;
    }

    public function hasDimensions(): bool
    {
        return $this->productDimension !== null;
    }

    public function getFormattedDimensions(): ?string
    {
        return $this->productDimension?->getFormattedDimensions();
    }

    public function getVolume(): ?float
    {
        return $this->productDimension?->getVolume();
    }

    // Shipping calculations

    public function getVolumetricWeight(float $factor = 5000): ?float
    {
        return $this->productDimension?->getVolumetricWeight($factor);
    }

    public function calculateShippingCost(?string $destination = 'domestic'): float
    {
        // Skip shipping calculation for virtual products
        if ($this->isVirtual || !$this->requiresShipping) {
            return 0.00;
        }

        $calculatorFactory = App::diGet(ShippingCalculatorFactory::class);
        $calculator = $calculatorFactory->createForProduct($this);

        return $calculator->calculate(
            $this->productWeight,
            $this->productDimension,
            $this->getVolumetricWeight(),
            $this->shippingClassId,
            $destination,
        );
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
     * @return Product
     */
    public function setId(int $id): Product
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
     * @return Product
     */
    public function setPublicId(UuidInterface $publicId): Product
    {
        $this->publicId = $publicId;

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
     * @return Product
     */
    public function setSku(string $sku): Product
    {
        $this->sku = $sku;

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
     * @return Product
     */
    public function setName(string $name): Product
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
     * @return Product
     */
    public function setSlug(string $slug): Product
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @param StockStatus $stockStatus
     *
     * @return Product
     */
    public function setStockStatus(StockStatus $stockStatus): Product
    {
        $this->stockStatus = $stockStatus;

        return $this;
    }

    /**
     * @param bool $is_active
     *
     * @return Product
     */
    public function setIs_active(bool $is_active): Product
    {
        $this->is_active = $is_active;

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
     * @return Product
     */
    public function setDescription(?string $description): Product
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
     * @return Product
     */
    public function setShortDescription(?string $shortDescription): Product
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    /**
     * @param null|int $categoryId
     *
     * @return Product
     */
    public function setCategoryId(?int $categoryId): Product
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getTaxClassId(): ?int
    {
        return $this->taxClassId;
    }

    /**
     * @param null|int $taxClassId
     *
     * @return Product
     */
    public function setTaxClassId(?int $taxClassId): Product
    {
        $this->taxClassId = $taxClassId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getBrandId(): ?int
    {
        return $this->brandId;
    }

    /**
     * @param null|int $brandId
     *
     * @return Product
     */
    public function setBrandId(?int $brandId): Product
    {
        $this->brandId = $brandId;

        return $this;
    }

    /**
     * @return int
     */
    public function getBaseCurrencyId(): int
    {
        return $this->baseCurrencyId;
    }

    /**
     * @param int $baseCurrencyId
     *
     * @return Product
     */
    public function setBaseCurrencyId(int $baseCurrencyId): Product
    {
        $this->baseCurrencyId = $baseCurrencyId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getStockStatusId(): ?int
    {
        return $this->stockStatusId;
    }

    /**
     * @param null|int $stockStatusId
     *
     * @return Product
     */
    public function setStockStatusId(?int $stockStatusId): Product
    {
        $this->stockStatusId = $stockStatusId;

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
     * @return Product
     */
    public function setIsTrackStock(bool $isTrackStock): Product
    {
        $this->isTrackStock = $isTrackStock;

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
     * @return Product
     */
    public function setStockQuantity(int $stockQuantity): Product
    {
        $this->stockQuantity = $stockQuantity;

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
     * @return Product
     */
    public function setMainImage(?string $mainImage): Product
    {
        $this->mainImage = $mainImage;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    /**
     * @param null|int $createdBy
     *
     * @return Product
     */
    public function setCreatedBy(?int $createdBy): Product
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getUpdatedBy(): ?int
    {
        return $this->updatedBy;
    }

    /**
     * @param null|int $updatedBy
     *
     * @return Product
     */
    public function setUpdatedBy(?int $updatedBy): Product
    {
        $this->updatedBy = $updatedBy;

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
     * @return Product
     */
    public function setAllowBackOrders(bool $allowBackOrders): Product
    {
        $this->allowBackOrders = $allowBackOrders;

        return $this;
    }

    /**
     * @return array
     */
    public function getRegionalPrices(): array
    {
        return $this->regionalPrices;
    }

    /**
     * @param array $regionalPrices
     *
     * @return Product
     */
    public function setRegionalPrices(array $regionalPrices): Product
    {
        $this->regionalPrices = $regionalPrices;

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
     * @return Product
     */
    public function setIsDownloadable(bool $isDownloadable): Product
    {
        $this->isDownloadable = $isDownloadable;

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
     * @return Product
     */
    public function setIsVirtual(bool $isVirtual): Product
    {
        $this->isVirtual = $isVirtual;

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
     * @return Product
     */
    public function setIsFeatured(bool $isFeatured): Product
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     *
     * @return Product
     */
    public function setIsActive(bool $isActive): Product
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusId(): int
    {
        return $this->statusId;
    }

    /**
     * @param int $statusId
     *
     * @return Product
     */
    public function setStatusId(int $statusId): Product
    {
        $this->statusId = $statusId;

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
     * @return Product
     */
    public function setProductVisibility(ProductVisibility $productVisibility): Product
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
     * @return Product
     */
    public function setProductWeight(?Weight $productWeight): Product
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
     * @return Product
     */
    public function setProductDimension(?Dimensions $productDimension): Product
    {
        $this->productDimension = $productDimension;

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
     * @return Product
     */
    public function setMainVideo(?string $mainVideo): Product
    {
        $this->mainVideo = $mainVideo;

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
     * @return Product
     */
    public function setTotalSales(int $totalSales): Product
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
     * @return Product
     */
    public function setIsOnSale(?int $isOnSale): Product
    {
        $this->isOnSale = $isOnSale;

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
     * @return Product
     */
    public function setAverageRating(float $averageRating): Product
    {
        $this->averageRating = $averageRating;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPriceIncludesTax(): bool
    {
        return $this->priceIncludesTax;
    }

    /**
     * @param bool $priceIncludesTax
     *
     * @return Product
     */
    public function setPriceIncludesTax(bool $priceIncludesTax): Product
    {
        $this->priceIncludesTax = $priceIncludesTax;

        return $this;
    }

    /**
     * @return int
     */
    public function getLowStockThreshold(): int
    {
        return $this->lowStockThreshold;
    }

    /**
     * @param int $lowStockThreshold
     *
     * @return Product
     */
    public function setLowStockThreshold(int $lowStockThreshold): Product
    {
        $this->lowStockThreshold = $lowStockThreshold;

        return $this;
    }

    /**
     * @return int
     */
    public function getMinOrderQuantity(): int
    {
        return $this->minOrderQuantity;
    }

    /**
     * @param int $minOrderQuantity
     *
     * @return Product
     */
    public function setMinOrderQuantity(int $minOrderQuantity): Product
    {
        $this->minOrderQuantity = $minOrderQuantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxOrderQuantity(): int
    {
        return $this->maxOrderQuantity;
    }

    /**
     * @param int $maxOrderQuantity
     *
     * @return Product
     */
    public function setMaxOrderQuantity(int $maxOrderQuantity): Product
    {
        $this->maxOrderQuantity = $maxOrderQuantity;

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
     * @return Product
     */
    public function setReviewCount(int $reviewCount): Product
    {
        $this->reviewCount = $reviewCount;

        return $this;
    }
}