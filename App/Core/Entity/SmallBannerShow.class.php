<?php

declare(strict_types=1);

class SmallBannerShow extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;

    protected const array RELATIONSHIPS = [
        'product' => [
            'class' => Product::class,
            'type' => 'one-to-one',
            'collection' => false,
            'foreign_key' => 'product_id',
        ],
    ];

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        prefix: '#',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'sm_banner_id', type: FieldType::INT)]
    private int $id;

    #[DisplayFormat(
        style: 'uuid',
        prefix: 'ID: ',
        suffix: ' (UUID)',
    )]
    #[EntityFieldId(name: 'public_id', type: FieldType::STRING)]
    private string $publicId;

    // Core configuration
    private SmallBannerClass $smallBannerClass; // enum
    private string $pageTarget = 'index';

    // Relationship
    private Product $product;

    // Custom content overrides (optional)
    private ?string $customTitle = null;
    private ?string $customTitleSpan = null;
    private ?string $customSubtitle = null;
    private ?string $customDescription = null;
    private ?string $customImageUrl = null;
    private ?string $customImageAltText = null;
    private ?string $customButtonText = null;
    private ?string $customButtonLink = null;

    // Display settings
    private Theme $smallBannerTheme = Theme::LIGHT; // enum
    private int $sortOrder = 0;

    // Control flags
    private bool $isActive = true;
    private ?DateTimeImmutable $validFrom = null;
    private ?DateTimeImmutable $validTo = null;

    /**
     * @return string
     */
    public function getPublicId(): string
    {
        return $this->publicId;
    }

    /**
     * @param string $publicId
     *
     * @return SmallBannerShow
     */
    public function setPublicId(string $publicId): SmallBannerShow
    {
        $this->publicId = $publicId;

        return $this;
    }

    /**
     * @return string
     */
    public function getPageTarget(): string
    {
        return $this->pageTarget;
    }

    /**
     * @param string $pageTarget
     *
     * @return SmallBannerShow
     */
    public function setPageTarget(string $pageTarget): SmallBannerShow
    {
        $this->pageTarget = $pageTarget;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCustomTitle(): ?string
    {
        return $this->customTitle;
    }

    /**
     * @param null|string $customTitle
     *
     * @return SmallBannerShow
     */
    public function setCustomTitle(?string $customTitle): SmallBannerShow
    {
        $this->customTitle = $customTitle;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCustomSubtitle(): ?string
    {
        return $this->customSubtitle;
    }

    /**
     * @param null|string $customSubtitle
     *
     * @return SmallBannerShow
     */
    public function setCustomSubtitle(?string $customSubtitle): SmallBannerShow
    {
        $this->customSubtitle = $customSubtitle;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCustomDescription(): ?string
    {
        return $this->customDescription;
    }

    /**
     * @param null|string $customDescription
     *
     * @return SmallBannerShow
     */
    public function setCustomDescription(?string $customDescription): SmallBannerShow
    {
        $this->customDescription = $customDescription;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCustomImageUrl(): ?string
    {
        return $this->customImageUrl;
    }

    /**
     * @param null|string $customImageUrl
     *
     * @return SmallBannerShow
     */
    public function setCustomImageUrl(?string $customImageUrl): SmallBannerShow
    {
        $this->customImageUrl = $customImageUrl;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCustomButtonText(): ?string
    {
        return $this->customButtonText;
    }

    /**
     * @param null|string $customButtonText
     *
     * @return SmallBannerShow
     */
    public function setCustomButtonText(?string $customButtonText): SmallBannerShow
    {
        $this->customButtonText = $customButtonText;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    /**
     * @param int $sortOrder
     *
     * @return SmallBannerShow
     */
    public function setSortOrder(int $sortOrder): SmallBannerShow
    {
        $this->sortOrder = $sortOrder;

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
     * @return SmallBannerShow
     */
    public function setIsActive(bool $isActive): SmallBannerShow
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getValidFrom(): ?DateTimeImmutable
    {
        return $this->validFrom;
    }

    /**
     * @param null|DateTimeImmutable $validFrom
     *
     * @return SmallBannerShow
     */
    public function setValidFrom(?DateTimeImmutable $validFrom): SmallBannerShow
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getValidTo(): ?DateTimeImmutable
    {
        return $this->validTo;
    }

    /**
     * @param null|DateTimeImmutable $validTo
     *
     * @return SmallBannerShow
     */
    public function setValidTo(?DateTimeImmutable $validTo): SmallBannerShow
    {
        $this->validTo = $validTo;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCustomButtonLink(): ?string
    {
        return $this->customButtonLink;
    }

    /**
     * @param null|string $customButtonLink
     *
     * @return SmallBannerShow
     */
    public function setCustomButtonLink(?string $customButtonLink): SmallBannerShow
    {
        $this->customButtonLink = $customButtonLink;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCustomImageAltText(): ?string
    {
        return $this->customImageAltText;
    }

    /**
     * @param null|string $customImageAltText
     *
     * @return SmallBannerShow
     */
    public function setCustomImageAltText(?string $customImageAltText): SmallBannerShow
    {
        $this->customImageAltText = $customImageAltText;

        return $this;
    }

    /**
     * @return Theme
     */
    public function getSmallBannerTheme(): Theme
    {
        return $this->smallBannerTheme;
    }

    /**
     * @param Theme $smallBannerTheme
     *
     * @return SmallBannerShow
     */
    public function setSmallBannerTheme(Theme $smallBannerTheme): SmallBannerShow
    {
        $this->smallBannerTheme = $smallBannerTheme;

        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * @param Product $product
     *
     * @return SmallBannerShow
     */
    public function setProduct(Product $product): SmallBannerShow
    {
        $this->product = $product;

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
     * @return SmallBannerShow
     */
    public function setId(int $id): SmallBannerShow
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return SmallBannerClass
     */
    public function getSmallBannerClass(): SmallBannerClass
    {
        return $this->smallBannerClass;
    }

    /**
     * @param SmallBannerClass $smallBannerClass
     *
     * @return SmallBannerShow
     */
    public function setSmallBannerClass(SmallBannerClass $smallBannerClass): SmallBannerShow
    {
        $this->smallBannerClass = $smallBannerClass;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCustomTitleSpan(): ?string
    {
        return $this->customTitleSpan;
    }

    /**
     * @param null|string $customTitleSpan
     *
     * @return SmallBannerShow
     */
    public function setCustomTitleSpan(?string $customTitleSpan): SmallBannerShow
    {
        $this->customTitleSpan = $customTitleSpan;

        return $this;
    }
}