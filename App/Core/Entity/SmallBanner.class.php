<?php

declare(strict_types=1);

use Ramsey\Uuid\UuidInterface;

class SmallBanner extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;

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
    private UuidInterface $publicId;

    // Core configuration
    private SmallBannerClass $smallBannerClass; // enum
    private string $pageTarget = 'index';

    // Relationship
    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        prefix: '#',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'product_id', type: FieldType::STRING)]
    private ?int $productId = null;

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
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return SmallBanner
     */
    public function setId(int $id): SmallBanner
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
     * @return SmallBanner
     */
    public function setPublicId(UuidInterface $publicId): SmallBanner
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
     * @return SmallBanner
     */
    public function setPageTarget(string $pageTarget): SmallBanner
    {
        $this->pageTarget = $pageTarget;
        return $this;
    }

    /**
     * @return null|int
     */
    public function getProductId(): ?int
    {
        return $this->productId;
    }

    /**
     * @param null|int $productId
     *
     * @return SmallBanner
     */
    public function setProductId(?int $productId): SmallBanner
    {
        $this->productId = $productId;
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
     * @return SmallBanner
     */
    public function setCustomTitle(?string $customTitle): SmallBanner
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
     * @return SmallBanner
     */
    public function setCustomSubtitle(?string $customSubtitle): SmallBanner
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
     * @return SmallBanner
     */
    public function setCustomDescription(?string $customDescription): SmallBanner
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
     * @return SmallBanner
     */
    public function setCustomImageUrl(?string $customImageUrl): SmallBanner
    {
        $this->customImageUrl = $customImageUrl;
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
     * @return SmallBanner
     */
    public function setCustomImageAltText(?string $customImageAltText): SmallBanner
    {
        $this->customImageAltText = $customImageAltText;

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
     * @return SmallBanner
     */
    public function setCustomButtonText(?string $customButtonText): SmallBanner
    {
        $this->customButtonText = $customButtonText;

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
     * @return SmallBanner
     */
    public function setCustomButtonLink(?string $customButtonLink): SmallBanner
    {
        $this->customButtonLink = $customButtonLink;

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
     * @return SmallBanner
     */
    public function setSortOrder(int $sortOrder): SmallBanner
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
     * @return SmallBanner
     */
    public function setIsActive(bool $isActive): SmallBanner
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
     * @return SmallBanner
     */
    public function setValidFrom(?DateTimeImmutable $validFrom): SmallBanner
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
     * @return SmallBanner
     */
    public function setValidTo(?DateTimeImmutable $validTo): SmallBanner
    {
        $this->validTo = $validTo;

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
     * @return SmallBanner
     */
    public function setSmallBannerTheme(Theme $smallBannerTheme): SmallBanner
    {
        $this->smallBannerTheme = $smallBannerTheme;

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
     * @return SmallBanner
     */
    public function setSmallBannerClass(SmallBannerClass $smallBannerClass): SmallBanner
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
     * @return SmallBanner
     */
    public function setCustomTitleSpan(?string $customTitleSpan): SmallBanner
    {
        $this->customTitleSpan = $customTitleSpan;

        return $this;
    }
}