<?php

declare(strict_types=1);

use Ramsey\Uuid\UuidInterface;

class ProductShow extends Entity
{
    protected const array RELATIONSHIPS = [
        'product' => Product::class,
        'stock_status' => StockStatus::class,
        'category' => Category::class,
        'brand' => Brand::class,
        'product_regional_price' => ProductRegionalPrice::class,
        'product_image_gallery' => ProductImageGallery::class,
        'product_variation' => ProductVariation::class,
        'variation_attribute' => VariationAttribute::class,
        'variation_type' => VariationType::class,
    ];

    private UuidInterface $publicId;

    // Core display fields
    private string $name;
    private string $slug;
    private string $sku;
    private ?string $description = null;
    private ?string $shortDescription = null;

    // Pricing
    /** @var ProductRegionalPrice[] */
    private array $regionalPrices = [];

    private ?string $formattedBasePrice = null;
    private ?string $formattedSalePrice = null;
    private bool $isOnSale = false;

    // Stock information (ADD THESE PROPERTIES)
    private StockStatus $stockStatus;
    private int $stockQuantity;
    private bool $allowBackOrders;
    private bool $isTrackStock;
    private string $stockDisplayText;

    // Variations (ADD THIS PROPERTY)
    private bool $hasVariations = false;

    /** @var ProductVariationShow[] */
    private array $variations = [];

    // Media
    private ?string $mainImage = null;

    /** @var ProductImageGallery[] */
    private array $galleryImages = [];

    private ?string $mainVideo = null;

    // Categorization
    private ?string $categoryName = null;
    private ?string $brandName = null;
    private array $tags = [];

    // Shipping
    private ?Weight $weight = null;
    private ?string $formattedWeight = null;
    private ?Dimensions $dimensions = null;
    private ?string $formattedDimensions = null;

    // SEO
    private ?string $metaTitle = null;
    private ?string $metaDescription = null;

    // Social/Engagement
    private float $averageRating = 0.0;
    private int $reviewCount = 0;
    private int $viewCount = 0;

    // Related products
    /** @var RelatedProduct[] */
    private array $relatedProducts = [];

    public function isTrackStock(): bool
    {
        return $this->isTrackStock;
    }

    public function hasVariations(): bool
    {
        return $this->hasVariations;
    }

    // Business logic methods (UPDATED - use StockStatus entity)
    public function isAvailable(): bool
    {
        $stockStatusCode = $this->stockStatus->getStockStatusCode();

        return $stockStatusCode === StockStatusCode::IN_STOCK
            || ($stockStatusCode === StockStatusCode::BACKORDERED && $this->allowBackOrders);
    }

    public function shouldShowAddToCart(): bool
    {
        return $this->isAvailable() && !$this->hasVariations;
    }

    // Additional helpful methods (UPDATED)
    public function getLowStockMessage(): ?string
    {
        if ($this->isTrackStock && $this->stockQuantity > 0 && $this->stockQuantity <= 5) {
            return "Only {$this->stockQuantity} left in stock!";
        }
        return null;
    }

    public function getStockStatusCode(): StockStatusCode
    {
        return $this->stockStatus->getStockStatusCode();
    }

    public function getStockStatusLabel(): string
    {
        return $this->stockStatus->getLabel();
    }

    // For variations
    public function hasAvailableVariations(): bool
    {
        if (!$this->hasVariations) {
            return false;
        }

        foreach ($this->variations as $variation) {
            if ($variation->isAvailable()) {
                return true;
            }
        }

        return false;
    }

    public function canBeOrdered(int $quantity = 1): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        $stockStatusCode = $this->stockStatus->getStockStatusCode();

        if ($this->isTrackStock && $stockStatusCode === StockStatusCode::IN_STOCK) {
            return $this->stockQuantity >= $quantity;
        }

        return true;
    }

    public function getStockDisplayText(): string
    {
        if (!$this->isTrackStock) {
            return 'Available';
        }

        $stockStatusCode = $this->stockStatus->getStockStatusCode();

        return match($stockStatusCode) {
            StockStatusCode::IN_STOCK => $this->stockQuantity > 5 ? 'In Stock' : "Only {$this->stockQuantity} left",
            StockStatusCode::OUT_OF_STOCK => 'Out of Stock',
            StockStatusCode::BACKORDERED => 'Backordered',
            StockStatusCode::PRE_ORDER => 'Pre-Order',
            StockStatusCode::DISCONTINUED => 'Discontinued',
            default => $this->stockStatus->getLabel(), // Fallback to the label from database
        };
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
     * @return array
     */
    public function getRegionalPrices(): array
    {
        return $this->regionalPrices;
    }

    /**
     * @param array $regionalPrices
     *
     * @return ProductShow
     */
    public function setRegionalPrices(array $regionalPrices): ProductShow
    {
        $this->regionalPrices = $regionalPrices;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFormattedBasePrice(): ?string
    {
        return $this->formattedBasePrice;
    }

    /**
     * @param null|string $formattedBasePrice
     *
     * @return ProductShow
     */
    public function setFormattedBasePrice(?string $formattedBasePrice): ProductShow
    {
        $this->formattedBasePrice = $formattedBasePrice;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFormattedSalePrice(): ?string
    {
        return $this->formattedSalePrice;
    }

    /**
     * @param null|string $formattedSalePrice
     *
     * @return ProductShow
     */
    public function setFormattedSalePrice(?string $formattedSalePrice): ProductShow
    {
        $this->formattedSalePrice = $formattedSalePrice;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsOnSale(): bool
    {
        return $this->isOnSale;
    }

    /**
     * @param bool $isOnSale
     *
     * @return ProductShow
     */
    public function setIsOnSale(bool $isOnSale): ProductShow
    {
        $this->isOnSale = $isOnSale;

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
    public function getHasVariations(): bool
    {
        return $this->hasVariations;
    }

    /**
     * @param bool $hasVariations
     *
     * @return ProductShow
     */
    public function setHasVariations(bool $hasVariations): ProductShow
    {
        $this->hasVariations = $hasVariations;

        return $this;
    }

    /**
     * @return array
     */
    public function getVariations(): array
    {
        return $this->variations;
    }

    /**
     * @param array $variations
     *
     * @return ProductShow
     */
    public function setVariations(array $variations): ProductShow
    {
        $this->variations = $variations;

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
     * @return array
     */
    public function getGalleryImages(): array
    {
        return $this->galleryImages;
    }

    /**
     * @param array $galleryImages
     *
     * @return ProductShow
     */
    public function setGalleryImages(array $galleryImages): ProductShow
    {
        $this->galleryImages = $galleryImages;

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
     * @return null|string
     */
    public function getCategoryName(): ?string
    {
        return $this->categoryName;
    }

    /**
     * @param null|string $categoryName
     *
     * @return ProductShow
     */
    public function setCategoryName(?string $categoryName): ProductShow
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getBrandName(): ?string
    {
        return $this->brandName;
    }

    /**
     * @param null|string $brandName
     *
     * @return ProductShow
     */
    public function setBrandName(?string $brandName): ProductShow
    {
        $this->brandName = $brandName;

        return $this;
    }

    /**
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param array $tags
     *
     * @return ProductShow
     */
    public function setTags(array $tags): ProductShow
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return null|Weight
     */
    public function getWeight(): ?Weight
    {
        return $this->weight;
    }

    /**
     * @param null|Weight $weight
     *
     * @return ProductShow
     */
    public function setWeight(?Weight $weight): ProductShow
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFormattedWeight(): ?string
    {
        return $this->formattedWeight;
    }

    /**
     * @param null|string $formattedWeight
     *
     * @return ProductShow
     */
    public function setFormattedWeight(?string $formattedWeight): ProductShow
    {
        $this->formattedWeight = $formattedWeight;

        return $this;
    }

    /**
     * @return null|Dimensions
     */
    public function getDimensions(): ?Dimensions
    {
        return $this->dimensions;
    }

    /**
     * @param null|Dimensions $dimensions
     *
     * @return ProductShow
     */
    public function setDimensions(?Dimensions $dimensions): ProductShow
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getFormattedDimensions(): ?string
    {
        return $this->formattedDimensions;
    }

    /**
     * @param null|string $formattedDimensions
     *
     * @return ProductShow
     */
    public function setFormattedDimensions(?string $formattedDimensions): ProductShow
    {
        $this->formattedDimensions = $formattedDimensions;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    /**
     * @param null|string $metaTitle
     *
     * @return ProductShow
     */
    public function setMetaTitle(?string $metaTitle): ProductShow
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    /**
     * @param null|string $metaDescription
     *
     * @return ProductShow
     */
    public function setMetaDescription(?string $metaDescription): ProductShow
    {
        $this->metaDescription = $metaDescription;

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
     * @return array
     */
    public function getRelatedProducts(): array
    {
        return $this->relatedProducts;
    }

    /**
     * @param array $relatedProducts
     *
     * @return ProductShow
     */
    public function setRelatedProducts(array $relatedProducts): ProductShow
    {
        $this->relatedProducts = $relatedProducts;

        return $this;
    }

    /**
     * @param string $stockDisplayText
     *
     * @return ProductShow
     */
    public function setStockDisplayText(string $stockDisplayText): ProductShow
    {
        $this->stockDisplayText = $stockDisplayText;

        return $this;
    }

    protected function getRelationShip(string $name): string
    {
        return static::RELATIONSHIPS[$name];
    }
}