<?php

declare(strict_types=1);

class ContentBlock extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;
    use MetadataTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'id', type: FieldType::INT)]
    private int $id;

    private int $sectionId;
    private BlockType $blockType;
    private ?string $title = null;
    private ?string $subtitle = null;
    private ?string $buttonText = null;
    private ?string $buttonLink = null;
    private ?string $pageTarget = null;
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
     * @return ContentBlock
     */
    public function setId(int $id): ContentBlock
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getSectionId(): int
    {
        return $this->sectionId;
    }

    /**
     * @param int $sectionId
     *
     * @return ContentBlock
     */
    public function setSectionId(int $sectionId): ContentBlock
    {
        $this->sectionId = $sectionId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     *
     * @return ContentBlock
     */
    public function setTitle(?string $title): ContentBlock
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
     * @return ContentBlock
     */
    public function setSubtitle(?string $subtitle): ContentBlock
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getButtonText(): ?string
    {
        return $this->buttonText;
    }

    /**
     * @param null|string $buttonText
     *
     * @return ContentBlock
     */
    public function setButtonText(?string $buttonText): ContentBlock
    {
        $this->buttonText = $buttonText;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getButtonLink(): ?string
    {
        return $this->buttonLink;
    }

    /**
     * @param null|string $buttonLink
     *
     * @return ContentBlock
     */
    public function setButtonLink(?string $buttonLink): ContentBlock
    {
        $this->buttonLink = $buttonLink;

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
     * @return ContentBlock
     */
    public function setSortOrder(int $sortOrder): ContentBlock
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
     * @return ContentBlock
     */
    public function setIsActive(bool $isActive): ContentBlock
    {
        $this->isActive = $isActive;

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
     * @return ContentBlock
     */
    public function setPageTarget(?string $pageTarget): ContentBlock
    {
        $this->pageTarget = $pageTarget;

        return $this;
    }

    /**
     * @return array
     */
    public function getBlockMetadata(): array
    {
        return $this->blockMetadata;
    }

    /**
     * @param array $blockMetadata
     *
     * @return ContentBlock
     */
    public function setBlockMetadata(array $blockMetadata): ContentBlock
    {
        $this->blockMetadata = $blockMetadata;

        return $this;
    }

    /**
     * @return BlockType
     */
    public function getBlockType(): BlockType
    {
        return $this->blockType;
    }

    /**
     * @param BlockType $blockType
     *
     * @return ContentBlock
     */
    public function setBlockType(BlockType $blockType): ContentBlock
    {
        $this->blockType = $blockType;

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
     * @return ContentBlock
     */
    public function setValidFrom(?DateTimeImmutable $validFrom): ContentBlock
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
     * @return ContentBlock
     */
    public function setValidTo(?DateTimeImmutable $validTo): ContentBlock
    {
        $this->validTo = $validTo;

        return $this;
    }
}