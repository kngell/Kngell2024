<?php

declare(strict_types=1);

class ProductCardResponse extends AbstractBaseEntityResponse
{
    private const string DEFAULT_BUTTON_TEXT = 'View Details';
    private const string DEFAULT_BUTTON_LINK = '#';
    private const string DEFAULT_CURRENCY = 'USD';

    public function __construct(
        array $image,
        ?ProductShow $product,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $product, $isDefault);
    }

    public function getProduct(): ?ProductShow
    {
        return $this->entity;
    }

    /**
     * Get product ID.
     */
    public function getId(): ?int
    {
        return $this->getProduct()?->getId();
    }

    /**
     * Get product public ID.
     */
    public function getPublicId(): ?string
    {
        return $this->getProduct()?->getPublicId()->toString();
    }

    /**
     * Get product name.
     */
    public function getName(): ?string
    {
        return $this->getProduct()?->getName();
    }

    /**
     * Get product slug.
     */
    public function getSlug(): ?string
    {
        return $this->getProduct()?->getSlug();
    }

    /**
     * Get product SKU.
     */
    public function getSku(): ?string
    {
        return $this->getProduct()?->getSku();
    }

    /**
     * Get product price.
     */
    public function getPrice(): ?string
    {
        $product = $this->getProduct();
        if (!$product) {
            return null;
        }

        return $this->formatPrice($product->getProductRegionalPrice()->getBasePrice());
    }

    public function getSalePrice(): ?string
    {
        $product = $this->getProduct();
        if (!$product || !$product->getProductRegionalPrice()->getSalePrice()()) {
            return null;
        }

        return $this->formatPrice($product->getProductRegionalPrice()->getSalePrice());
    }

    public function isOnSale(): bool
    {
        return $this->getProduct()?->getProductRegionalPrice()->getIsOnSale() ?? false;
    }

    /**
     * Get discount percentage.
     */
    public function getDiscountPercent(): ?int
    {
        $product = $this->getProduct();
        if (!$product || !$product->getProductRegionalPrice()->getIsOnSale()) {
            return null;
        }

        return $product->getProductRegionalPrice()->getDiscountPercent();
    }

    /**
     * Get product rating.
     */
    public function getRating(): float
    {
        return $this->getProduct()?->getAverageRating() ?? 0.0;
    }

    /**
     * Get review count.
     */
    public function getReviewCount(): int
    {
        return $this->getProduct()?->getReviewCount() ?? 0;
    }

    /**
     * Get view count.
     */
    public function getViewCount(): int
    {
        return $this->getProduct()?->getViewCount() ?? 0;
    }

    /**
     * Get product URL.
     */
    public function getUrl(): string
    {
        $product = $this->getProduct();
        if (!$product) {
            return self::DEFAULT_BUTTON_LINK;
        }

        return "/product/{$product->getSlug()}";
    }

    /**
     * Get button text.
     */
    public function getButtonText(): string
    {
        return self::DEFAULT_BUTTON_TEXT;
    }

    /**
     * Get button link.
     */
    public function getButtonLink(): string
    {
        return $this->getUrl();
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->getProduct()?->getStockStatus()->getStockStatusCode() === StockStatusCode::IN_STOCK ?? false;
    }

    /**
     * Get stock status text.
     */
    public function getStockStatus(): string
    {
        $product = $this->getProduct();
        if (!$product) {
            return 'Unknown';
        }

        return $product->getStockStatus()->getLabel();
    }

    /**
     * Get stock quantity.
     */
    public function getStockQuantity(): int
    {
        return $this->getProduct()?->getStockQuantity() ?? 0;
    }

    /**
     * Get product categories.
     */
    public function getCategories(): array
    {
        $product = $this->getProduct();
        if (!$product) {
            return [];
        }

        return [$product->getCategory()];
    }

    /**
     * Get main category.
     */
    public function getMainCategory(): ?Category
    {
        return $this->getProduct()?->getCategory();
    }

    /**
     * Get brand.
     */
    public function getBrand(): ?Brand
    {
        return $this->getProduct()?->getBrand();
    }

    /**
     * Get short description.
     */
    public function getShortDescription(): ?string
    {
        return $this->getProduct()?->getShortDescription();
    }

    /**
     * Get full description.
     */
    public function getDescription(): ?string
    {
        return $this->getProduct()?->getDescription();
    }

    /**
     * Check if product is featured.
     */
    public function isFeatured(): bool
    {
        return $this->getProduct()?->getIsFeatured() ?? false;
    }

    /**
     * Check if product allows backorders.
     */
    public function allowsBackOrders(): bool
    {
        return $this->getProduct()?->getAllowBackOrders() ?? false;
    }

    /**
     * Check if product is virtual.
     */
    public function isVirtual(): bool
    {
        return $this->getProduct()?->getIsVirtual() ?? false;
    }

    /**
     * Check if product is downloadable.
     */
    public function isDownloadable(): bool
    {
        return $this->getProduct()?->getIsDownloadable() ?? false;
    }

    /**
     * Get total sales count.
     */
    public function getTotalSales(): int
    {
        return $this->getProduct()?->getTotalSales() ?? 0;
    }

    /**
     * Get product weight.
     */
    public function getWeight(): ?Weight
    {
        return $this->getProduct()?->getProductWeight();
    }

    /**
     * Get product dimensions.
     */
    public function getDimensions(): ?Dimensions
    {
        return $this->getProduct()?->getProductDimension();
    }

    /**
     * Get product image gallery.
     */
    public function getImageGallery(): array
    {
        return $this->getProduct()?->getProductImageGallery() ?? [];
    }

    /**
     * Get product variations.
     */
    public function getVariations(): array
    {
        return $this->getProduct()?->getProductVariationShow() ?? [];
    }

    /**
     * Check if product has variations.
     */
    public function hasVariations(): bool
    {
        return !empty($this->getVariations());
    }

    /**
     * Get product attributes summary.
     */
    public function getAttributesSummary(): array
    {
        $product = $this->getProduct();
        if (!$product) {
            return [];
        }

        $summary = [];

        if ($brand = $product->getBrand()) {
            $summary['brand'] = $brand->getName();
        }

        if ($category = $product->getCategory()) {
            $summary['category'] = $category->getName();
        }

        if ($weight = $product->getProductWeight()) {
            $summary['weight'] = $weight;
        }

        if ($dimensions = $product->getProductDimension()) {
            $summary['dimensions'] = $dimensions;
        }

        return $summary;
    }

    /**
     * Get SEO data.
     */
    public function getSeoData(): array
    {
        $product = $this->getProduct();
        if (!$product) {
            return [];
        }

        return [
            'title' => $product->getName(),
            'description' => $product->getShortDescription(),
            'keywords' => $this->generateKeywords($product),
        ];
    }

    /**
     * Check if product is new (created within last 30 days).
     */
    public function isNew(): bool
    {
        $product = $this->getProduct();
        if (!$product) {
            return false;
        }

        $createdAt = $product->getCreatedAt();
        $thirtyDaysAgo = new DateTime('-30 days');

        return $createdAt > $thirtyDaysAgo;
    }

    /**
     * Get product status.
     */
    public function getStatus(): string
    {
        return $this->getProduct()?->getProductStatus()->getName() ?? 'Unknown';
    }

    /**
     * Check if product is active.
     */
    public function isActive(): bool
    {
        return $this->getProduct()?->getProductStatus()->getIsActive() ?? false;
    }

    /**
     * Get main video URL if available.
     */
    public function getVideoUrl(): ?string
    {
        return $this->getProduct()?->getMainVideo();
    }

    /**
     * Check if product has video.
     */
    public function hasVideo(): bool
    {
        return $this->getVideoUrl() !== null;
    }

    // ==================== PRIVATE HELPER METHODS ====================

    private function formatPrice(?float $price): ?string
    {
        if ($price === null) {
            return null;
        }

        return number_format($price, 2) . ' ' . self::DEFAULT_CURRENCY;
    }

    private function generateKeywords(ProductShow $product): string
    {
        $keywords = [];

        $keywords[] = $product->getName();
        $keywords[] = $product->getBrand()?->getName();
        $keywords[] = $product->getCategory()?->getName();
        $keywords[] = $product->getSku();

        if ($product->getIsOnSale()) {
            $keywords[] = 'sale';
            $keywords[] = 'discount';
        }

        if ($product->getIsFeatured()) {
            $keywords[] = 'featured';
        }

        if ($this->isNew()) {
            $keywords[] = 'new';
        }

        return implode(', ', array_filter($keywords));
    }
}
