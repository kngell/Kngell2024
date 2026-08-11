<?php

declare(strict_types=1);

class ProductCardResponse extends AbstractBaseEntityResponse
{
    use EntityDisplayTrait;
    use ProductPriceTrait;

    private const string DEFAULT_BUTTON_TEXT = 'View Details';
    private const string DEFAULT_BUTTON_LINK = '#';

    public function __construct(
        array $image,
        private HtmlSectionPresentationService $presenter,
        ?ProductCollection $product,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $product, $isDefault);
    }

    public function getEntity(): ?ProductCollection
    {
        return $this->entity;
    }

    // ─── Product Data ────────────────────────────────────────────

    public function getId(): null|int|string
    {
        return $this->show($this->getEntity(), 'id');
    }

    public function getPublicId(): ?string
    {
        return $this->getEntity()?->getPublicId()->toString();
    }

    public function getName(): ?string
    {
        return $this->presenter->showField($this->getEntity(), 'name') ?? null;
    }

    public function getSlug(): ?string
    {
        return $this->presenter->showField($this->getEntity(), 'slug') ?? null;
    }

    public function getSku(): ?string
    {
        return $this->presenter->showField($this->getEntity(), 'sku') ?? null;
    }

    public function getOriginalPrice(): ?string
    {
        $regionalPrice = $this->getRegionalPrice();
        if (!$regionalPrice) {
            return null;
        }

        $comparePrice = $regionalPrice->getComparePrice();
        if ($comparePrice) {
            return $this->show($regionalPrice, 'compare_price');
        }

        return $this->show($regionalPrice, 'base_price');
    }

    // ─── Product Details ──────────────────────────────────────────

    public function getRating(): float
    {
        return (float) $this->presenter->showField($this->getEntity(), 'average_rating') ?? 0.0;
    }

    public function getReviewCount(): int
    {
        return (int) $this->presenter->showField($this->getEntity(), 'reviewCount') ?? 0;
    }

    public function getUrl(): string
    {
        $product = $this->getEntity();
        if (!$product) {
            return self::DEFAULT_BUTTON_LINK;
        }

        return "/product/{$product->getSlug()}";
    }

    public function getButtonText(): string
    {
        return self::DEFAULT_BUTTON_TEXT;
    }

    public function getButtonLink(): string
    {
        return $this->getUrl();
    }

    public function isInStock(): bool
    {
        return $this->getEntity()?->getStockStatus()->getStockStatusCode() === StockStatusCode::IN_STOCK ?? false;
    }

    public function getStockStatus(): string
    {
        $product = $this->getEntity();
        if (!$product) {
            return 'Unknown';
        }

        return $product->getStockStatus()->getLabel();
    }

    public function getStockQuantity(): int
    {
        return $this->presenter->showField($this->getEntity(), 'stock_quantity') ?? 0;
    }

    public function getCategories(): array
    {
        $product = $this->getEntity();
        if (!$product) {
            return [];
        }

        return [$product->getCategory()];
    }

    public function getMainCategory(): ?Category
    {
        return $this->getEntity()?->getCategory();
    }

    public function getBrand(): ?Brand
    {
        return $this->getEntity()?->getBrand();
    }

    public function getShortDescription(): ?string
    {
        return $this->presenter->showField(
            $this->getEntity(),
            'short_description',
        );
    }

    public function getDescription(): ?string
    {
        return $this->presenter->showField(
            $this->getEntity(),
            'description',
        );
    }

    public function isFeatured(): bool
    {
        return $this->getEntity()?->getIsFeatured() ?? false;
    }

    public function allowsBackOrders(): bool
    {
        return $this->getEntity()?->getAllowBackOrders() ?? false;
    }

    public function isVirtual(): bool
    {
        return $this->getEntity()?->getIsVirtual() ?? false;
    }

    public function isDownloadable(): bool
    {
        return $this->getEntity()?->getIsDownloadable() ?? false;
    }

    public function getTotalSales(): int
    {
        return $this->getEntity()?->getTotalSales() ?? 0;
    }

    public function getWeight(): ?string
    {
        return $this->presenter->showField(
            $this->getEntity(),
            'product_weight',
        );
    }

    public function getDimensions(): ?string
    {
        return $this->presenter->showField(
            $this->getEntity(),
            'product_dimension',
        );
    }

    public function getAttributesSummary(): array
    {
        $product = $this->getEntity();
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

    public function getSeoData(): array
    {
        $product = $this->getEntity();
        if (!$product) {
            return [];
        }

        return [
            'title' => $this->getName(),
            'description' => $this->getShortDescription(),
            'keywords' => $this->generateKeywords($product),
        ];
    }

    public function isNew(): bool
    {
        $product = $this->getEntity();
        if (!$product) {
            return false;
        }

        $createdAt = $product->getCreatedAt();
        $thirtyDaysAgo = new DateTime('-30 days');

        return $createdAt > $thirtyDaysAgo;
    }

    public function getStatus(): string
    {
        return $this->presenter->showRelated(
            $this->getEntity(),
            'productStatus',
            'name',
        ) ?? 'Unknown';
    }

    public function getVideoUrl(): ?string
    {
        return $this->getEntity()?->getMainVideo();
    }

    public function hasVideo(): bool
    {
        return $this->getVideoUrl() !== null;
    }

    // ─── Private Helper ───────────────────────────────────────────

    private function generateKeywords(ProductCollection $product): string
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