<?php

declare(strict_types=1);

class FooterAbout extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    private string $content;
    private ?string $logoUrl;
    private ?string $logoIcon;
    private ?string $logoAlt = 'Logo';
    private ?string $logoLink = '/';
    private int $sortOrder = 0;
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
     * @return footerAbout
     */
    public function setId(int $id): footerAbout
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return footerAbout
     */
    public function setContent(string $content): footerAbout
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    /**
     * @param null|string $logoUrl
     *
     * @return footerAbout
     */
    public function setLogoUrl(?string $logoUrl): footerAbout
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogoIcon(): ?string
    {
        return $this->logoIcon;
    }

    /**
     * @param null|string $logoIcon
     *
     * @return footerAbout
     */
    public function setLogoIcon(?string $logoIcon): footerAbout
    {
        $this->logoIcon = $logoIcon;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogoAlt(): ?string
    {
        return $this->logoAlt;
    }

    /**
     * @param null|string $logoAlt
     *
     * @return footerAbout
     */
    public function setLogoAlt(?string $logoAlt): footerAbout
    {
        $this->logoAlt = $logoAlt;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLogoLink(): ?string
    {
        return $this->logoLink;
    }

    /**
     * @param null|string $logoLink
     *
     * @return footerAbout
     */
    public function setLogoLink(?string $logoLink): footerAbout
    {
        $this->logoLink = $logoLink;

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
     * @return footerAbout
     */
    public function setIsActive(bool $isActive): footerAbout
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
     * @return footerAbout
     */
    public function setValidFrom(?DateTimeImmutable $validFrom): footerAbout
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
     * @return footerAbout
     */
    public function setValidTo(?DateTimeImmutable $validTo): footerAbout
    {
        $this->validTo = $validTo;

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
     * @return FooterAbout
     */
    public function setSortOrder(int $sortOrder): FooterAbout
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}