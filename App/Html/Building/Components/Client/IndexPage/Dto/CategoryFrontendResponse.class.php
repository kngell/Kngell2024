<?php

declare(strict_types=1);

class CategoryFrontendResponse extends AbstractBaseEntityResponse
{
    private const string DEFAULT_BUTTON_TEXT = 'Explore';
    private const string DEFAULT_BUTTON_LINK = '#';

    public function __construct(
        array $image,
        ?Category $category,
        bool $isDefault = false,
    ) {
        parent::__construct($image, $category, $isDefault);
    }

    public function getCategory(): ?Category
    {
        return $this->entity;
    }

    public function getName(): ?string
    {
        return $this->getCategory()?->getName();
    }

    public function getSlug(): ?string
    {
        return $this->getCategory()?->getSlug();
    }

    public function getIcon(): ?string
    {
        return $this->getCategory()?->getIcon();
    }

    public function getShortDescription(): ?string
    {
        return $this->getCategory()?->getShortDescription();
    }

    public function getDescription(): ?string
    {
        return $this->getCategory()?->getDescription();
    }

    public function getUrl(): string
    {
        $category = $this->getCategory();
        if (!$category) {
            return self::DEFAULT_BUTTON_LINK;
        }

        $customUrl = $category->getCustomUrl();
        if ($customUrl) {
            return $customUrl;
        }

        return "/category/{$category->getSlug()}";
    }

    public function getButtonText(): string
    {
        return self::DEFAULT_BUTTON_TEXT;
    }

    public function getButtonLink(): string
    {
        return $this->getUrl();
    }

    public function isFeatured(): bool
    {
        return $this->getCategory()?->getIsFeatured() ?? false;
    }

    public function getProductCount(): ?int
    {
        return $this->getCategory()?->getProductsCount();
    }

    public function getCssClass(): ?string
    {
        return $this->getCategory()?->getCssClass();
    }

    public function getBackgroundColor(): ?string
    {
        return $this->getCategory()?->getBackgroundColor();
    }

    public function getTextColor(): ?string
    {
        return $this->getCategory()?->getTextColor();
    }

    public function getMetaTitle(): ?string
    {
        return $this->getCategory()?->getMetaTitle();
    }

    public function getMetaDescription(): ?string
    {
        return $this->getCategory()?->getMetaDescription();
    }

    public function getLevel(): int
    {
        return $this->getCategory()?->getLevel() ?? 0;
    }

    public function hasChildren(): bool
    {
        $category = $this->getCategory();
        if (!$category) {
            return false;
        }

        // Assuming you have a way to check children
        return $category->getChildren()->count() > 0;
    }

    public function getChildrenCount(): int
    {
        $category = $this->getCategory();
        if (!$category) {
            return 0;
        }

        return $category->getChildren()->count();
    }

    public function hasCustomContent(): bool
    {
        $category = $this->getCategory();
        if (!$category) {
            return false;
        }

        return $category->getCustomUrl() !== null
            || $category->getTemplate() !== null
            || $category->getCssClass() !== null;
    }

    public function showInMenu(): bool
    {
        return $this->getCategory()?->getShowInMenu() ?? true;
    }

    public function showInFooter(): bool
    {
        return $this->getCategory()?->getShowInFooter() ?? false;
    }

    public function getPriceRange(): ?array
    {
        $category = $this->getCategory();
        if (!$category) {
            return null;
        }

        $minPrice = $category->getMinPrice();
        $maxPrice = $category->getMaxPrice();

        if (!$minPrice && !$maxPrice) {
            return null;
        }

        return [
            'min' => $minPrice?->getAmount()->toFloat(),
            'max' => $maxPrice?->getAmount()->toFloat(),
            'currency' => $minPrice?->getCurrency()->getCurrencyCode() ?? 'USD',
        ];
    }

    public function getSortOptions(): array
    {
        $category = $this->getCategory();
        if (!$category) {
            return ['name' => 'Name'];
        }

        // Parse the default sort string or provide predefined options
        $defaultSort = $category->getDefaultSort();

        return [
            'name' => 'Name',
            'price_low' => 'Price: Low to High',
            'price_high' => 'Price: High to Low',
            'newest' => 'Newest',
            'popular' => 'Popular',
        ];
    }

    public function getSeoData(): array
    {
        $category = $this->getCategory();
        if (!$category) {
            return [];
        }

        return [
            'title' => $category->getMetaTitle() ?? $category->getName(),
            'description' => $category->getMetaDescription() ?? $category->getShortDescription(),
            'keywords' => $category->getMetaKeywords(),
            'og_title' => $category->getOgTitle(),
            'og_description' => $category->getOgDescription(),
            'og_image' => $category->getOgImage(),
            'canonical_url' => $category->getCanonicalUrl(),
        ];
    }
}