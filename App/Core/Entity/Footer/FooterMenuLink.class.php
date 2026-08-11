<?php

declare(strict_types=1);

class FooterMenuLink extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    private int $columnId;
    private string $title;
    private int $sortOrder = 0;
    private bool $isActive = true;
    private ?string $url = null;
    private TargetAttr $linkTarget = TargetAttr::SELF;
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
     * @return FooterMenuLink
     */
    public function setId(int $id): FooterMenuLink
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getColumnId(): int
    {
        return $this->columnId;
    }

    /**
     * @param int $columnId
     *
     * @return FooterMenuLink
     */
    public function setColumnId(int $columnId): FooterMenuLink
    {
        $this->columnId = $columnId;

        return $this;
    }

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
     * @return FooterMenuLink
     */
    public function setTitle(string $title): FooterMenuLink
    {
        $this->title = $title;

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
     * @return FooterMenuLink
     */
    public function setSortOrder(int $sortOrder): FooterMenuLink
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
     * @return FooterMenuLink
     */
    public function setIsActive(bool $isActive): FooterMenuLink
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
     * @return FooterMenuLink
     */
    public function setValidFrom(?DateTimeImmutable $validFrom): FooterMenuLink
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
     * @return FooterMenuLink
     */
    public function setValidTo(?DateTimeImmutable $validTo): FooterMenuLink
    {
        $this->validTo = $validTo;

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
     * @return FooterMenuLink
     */
    public function setUrl(?string $url): FooterMenuLink
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return TargetAttr
     */
    public function getLinkTarget(): TargetAttr
    {
        return $this->linkTarget;
    }

    /**
     * @param TargetAttr $linkTarget
     *
     * @return FooterMenuLink
     */
    public function setLinkTarget(TargetAttr $linkTarget): FooterMenuLink
    {
        $this->linkTarget = $linkTarget;

        return $this;
    }
}