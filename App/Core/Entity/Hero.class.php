<?php

declare(strict_types=1);

use Ramsey\Uuid\UuidInterface;

class Hero extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        prefix: '#',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'hero_id', type: FieldType::INT)]
    private int $id;

    #[DisplayFormat(
        style: 'uuid',
        prefix: 'ID: ',
        suffix: ' (UUID)',
    )]
    #[EntityFieldId(name: 'public_id', type: FieldType::STRING)]
    private UuidInterface $publicId;

    private string $title;
    private ?string $specializedTitle = null;
    private ?string $subtitle = null;
    private ?string $introduction = null;
    private string $imageUrl;
    private ?string $imageAlt = null;
    private ?string $mobileImageUrl = null;
    private ?string $ctaText = null;
    private ?string $ctaLink = null;
    private ?string $ctaSecondaryText = null;
    private ?string $ctaSecondaryLink = null;
    private int $overlayOpacity = 50;
    private bool $isActive = true;
    private int $sortOrder = 0;
    private ?string $pageTarget = null;
    private ?DateTimeImmutable $validFrom = null;
    private ?DateTimeImmutable $validTo = null;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Hero
     */
    public function setTitle(string $title): Hero
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSubtitle(): ?string
    {
        return $this->subtitle;
    }

    /**
     * @param null|string $subtitle
     *
     * @return Hero
     */
    public function setSubtitle(?string $subtitle): Hero
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     *
     * @return Hero
     */
    public function setImageUrl(string $imageUrl): Hero
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getImageAlt(): ?string
    {
        return $this->imageAlt;
    }

    /**
     * @param null|string $imageAlt
     *
     * @return Hero
     */
    public function setImageAlt(?string $imageAlt): Hero
    {
        $this->imageAlt = $imageAlt;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMobileImageUrl(): ?string
    {
        return $this->mobileImageUrl;
    }

    /**
     * @param null|string $mobileImageUrl
     *
     * @return Hero
     */
    public function setMobileImageUrl(?string $mobileImageUrl): Hero
    {
        $this->mobileImageUrl = $mobileImageUrl;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCtaText(): ?string
    {
        return $this->ctaText;
    }

    /**
     * @param null|string $ctaText
     *
     * @return Hero
     */
    public function setCtaText(?string $ctaText): Hero
    {
        $this->ctaText = $ctaText;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCtaLink(): ?string
    {
        return $this->ctaLink;
    }

    /**
     * @param null|string $ctaLink
     *
     * @return Hero
     */
    public function setCtaLink(?string $ctaLink): Hero
    {
        $this->ctaLink = $ctaLink;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCtaSecondaryText(): ?string
    {
        return $this->ctaSecondaryText;
    }

    /**
     * @param null|string $ctaSecondaryText
     *
     * @return Hero
     */
    public function setCtaSecondaryText(?string $ctaSecondaryText): Hero
    {
        $this->ctaSecondaryText = $ctaSecondaryText;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCtaSecondaryLink(): ?string
    {
        return $this->ctaSecondaryLink;
    }

    /**
     * @param null|string $ctaSecondaryLink
     *
     * @return Hero
     */
    public function setCtaSecondaryLink(?string $ctaSecondaryLink): Hero
    {
        $this->ctaSecondaryLink = $ctaSecondaryLink;

        return $this;
    }

    /**
     * @return int
     */
    public function getOverlayOpacity(): int
    {
        return $this->overlayOpacity;
    }

    /**
     * @param int $overlayOpacity
     *
     * @return Hero
     */
    public function setOverlayOpacity(int $overlayOpacity): Hero
    {
        $this->overlayOpacity = $overlayOpacity;

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
     * @return Hero
     */
    public function setIsActive(bool $isActive): Hero
    {
        $this->isActive = $isActive;

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
     * @return Hero
     */
    public function setSortOrder(int $sortOrder): Hero
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPageTarget(): ?string
    {
        return $this->pageTarget;
    }

    /**
     * @param null|string $pageTarget
     *
     * @return Hero
     */
    public function setPageTarget(?string $pageTarget): Hero
    {
        $this->pageTarget = $pageTarget;

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
     * @return Hero
     */
    public function setValidFrom(?DateTimeImmutable $validFrom): Hero
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
     * @return Hero
     */
    public function setValidTo(?DateTimeImmutable $validTo): Hero
    {
        $this->validTo = $validTo;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSpecializedTitle(): ?string
    {
        return $this->specializedTitle;
    }

    /**
     * @param null|string $specializedTitle
     *
     * @return Hero
     */
    public function setSpecializedTitle(?string $specializedTitle): Hero
    {
        $this->specializedTitle = $specializedTitle;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    /**
     * @param null|string $introduction
     *
     * @return Hero
     */
    public function setIntroduction(?string $introduction): Hero
    {
        $this->introduction = $introduction;

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
     * @return Hero
     */
    public function setPublicId(UuidInterface $publicId): Hero
    {
        $this->publicId = $publicId;

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
     * @return Hero
     */
    public function setId(int $id): Hero
    {
        $this->id = $id;

        return $this;
    }
}