<?php

declare(strict_types=1);

class Category extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;

    #[NotPersisted()]
    private CollectionInterface $children;

    #[EntityFieldId(name: 'cat_id')]
    private string $id;

    private string $name;
    private string $slug;
    private int $parentId;
    private int $level = 0;
    private null|string $path = null;
    private null|int $orderIndex = 0;
    private bool $isActive = true;
    private null|string $metaTitle;
    private null|string $meta_description;
    private null|string $description;
    private null|string $imageUrl;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return Category
     */
    public function setId(string $id): Category
    {
        $this->id = $id;

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
     * @return Category
     */
    public function setName(string $name): Category
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return Category
     */
    public function setSlug(string $slug): Category
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     *
     * @return Category
     */
    public function setParentId(int $parentId): Category
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return Category
     */
    public function setLevel(int $level): Category
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @param null|string $path
     *
     * @return Category
     */
    public function setPath(?string $path): Category
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getOrderIndex(): ?int
    {
        return $this->orderIndex;
    }

    /**
     * @param null|int $orderIndex
     *
     * @return Category
     */
    public function setOrderIndex(?int $orderIndex): Category
    {
        $this->orderIndex = $orderIndex;

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
     * @return Category
     */
    public function setIsActive(bool $isActive): Category
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    /**
     * @param null|string $metaTitle
     *
     * @return Category
     */
    public function setMetaTitle(?string $metaTitle): Category
    {
        $this->metaTitle = $metaTitle;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getMeta_description(): ?string
    {
        return $this->meta_description;
    }

    /**
     * @param null|string $meta_description
     *
     * @return Category
     */
    public function setMeta_description(?string $meta_description): Category
    {
        $this->meta_description = $meta_description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     *
     * @return Category
     */
    public function setDescription(?string $description): Category
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    /**
     * @param null|string $imageUrl
     *
     * @return Category
     */
    public function setImageUrl(?string $imageUrl): Category
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function initChildren(): void
    {
        $this->children = new Collection();
    }

    /**
     * @return CollectionInterface
     */
    public function getChildren(): CollectionInterface
    {
        return $this->children;
    }

    /**
     * @param Category $category
     *
     * @return Category
     */
    public function addChildren(Category $category): Category
    {
        $this->children->add($category);
        return $this;
    }
}