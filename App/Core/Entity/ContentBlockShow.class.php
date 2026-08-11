<?php

declare(strict_types=1);

class ContentBlockShow extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;
    use MetadataTrait;

    protected const array RELATIONSHIPS = [
        'product' => [
            'class' => Product::class,
            'type' => 'one-to-one',
            'collection' => false,
            'foreign_key' => 'product_id',
        ],
        'category' => [
            'class' => Category::class,
            'type' => 'one-to-one',
            'collection' => false,
            'foreign_key' => 'category_id',
        ],
    ];

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
    private ?Product $product = null;
    private ?Category $category = null;

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
     * @return ContentBlockShow
     */
    public function setId(int $id): ContentBlockShow
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
     * @return ContentBlockShow
     */
    public function setSectionId(int $sectionId): ContentBlockShow
    {
        $this->sectionId = $sectionId;

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
     * @return ContentBlockShow
     */
    public function setBlockType(BlockType $blockType): ContentBlockShow
    {
        $this->blockType = $blockType;

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
     * @return ContentBlockShow
     */
    public function setTitle(?string $title): ContentBlockShow
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
     * @return ContentBlockShow
     */
    public function setSubtitle(?string $subtitle): ContentBlockShow
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
     * @return ContentBlockShow
     */
    public function setButtonText(?string $buttonText): ContentBlockShow
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
     * @return ContentBlockShow
     */
    public function setButtonLink(?string $buttonLink): ContentBlockShow
    {
        $this->buttonLink = $buttonLink;

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
     * @return ContentBlockShow
     */
    public function setPageTarget(?string $pageTarget): ContentBlockShow
    {
        $this->pageTarget = $pageTarget;

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
     * @return ContentBlockShow
     */
    public function setSortOrder(int $sortOrder): ContentBlockShow
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
     * @return ContentBlockShow
     */
    public function setIsActive(bool $isActive): ContentBlockShow
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
     * @return ContentBlockShow
     */
    public function setValidFrom(?DateTimeImmutable $validFrom): ContentBlockShow
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
     * @return ContentBlockShow
     */
    public function setValidTo(?DateTimeImmutable $validTo): ContentBlockShow
    {
        $this->validTo = $validTo;

        return $this;
    }

    /**
     * @return null|Product
     */
    public function getProduct(): ?Product
    {
        return $this->product;
    }

    /**
     * @param null|Product $product
     *
     * @return ContentBlockShow
     */
    public function setProduct(?Product $product): ContentBlockShow
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return null|Category
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param null|Category $category
     *
     * @return ContentBlockShow
     */
    public function setCategory(?Category $category): ContentBlockShow
    {
        $this->category = $category;

        return $this;
    }
}