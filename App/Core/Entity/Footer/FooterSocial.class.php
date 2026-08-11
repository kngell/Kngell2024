<?php

declare(strict_types=1);

class FooterSocial extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[NotPersisted()]
    private string $defaultTableName = 'footer_social_link';

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    private string $platform;
    private string $name;
    private ?string $url = null;
    private ?string $icon = null;
    private ?string $iconClass = null;
    private int $sortOrder = 0;
    private bool $isActive = true;
    private ?DateTimeImmutable $validFrom = null;
    private ?DateTimeImmutable $validTo = null;

    /**
     * @return string
     */
    public function getDefaultTableName(): string
    {
        return $this->defaultTableName;
    }

    /**
     * @param string $defaultTableName
     *
     * @return FooterSocial
     */
    public function setDefaultTableName(string $defaultTableName): FooterSocial
    {
        $this->defaultTableName = $defaultTableName;

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
     * @return FooterSocial
     */
    public function setId(int $id): FooterSocial
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     *
     * @return FooterSocial
     */
    public function setPlatform(string $platform): FooterSocial
    {
        $this->platform = $platform;

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
     * @return FooterSocial
     */
    public function setName(string $name): FooterSocial
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param null|string $url
     *
     * @return FooterSocial
     */
    public function setUrl(?string $url): FooterSocial
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * @param null|string $icon
     *
     * @return FooterSocial
     */
    public function setIcon(?string $icon): FooterSocial
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getIconClass(): ?string
    {
        return $this->iconClass;
    }

    /**
     * @param null|string $iconClass
     *
     * @return FooterSocial
     */
    public function setIconClass(?string $iconClass): FooterSocial
    {
        $this->iconClass = $iconClass;

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
     * @return FooterSocial
     */
    public function setSortOrder(int $sortOrder): FooterSocial
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
     * @return FooterSocial
     */
    public function setIsActive(bool $isActive): FooterSocial
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
     * @return FooterSocial
     */
    public function setValidFrom(?DateTimeImmutable $validFrom): FooterSocial
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
     * @return FooterSocial
     */
    public function setValidTo(?DateTimeImmutable $validTo): FooterSocial
    {
        $this->validTo = $validTo;

        return $this;
    }
}