<?php

declare(strict_types=1);

class Category extends Entity
{
    #[EntityFieldId(name: 'category_id')]
    private string $categoryId;
    private string $categoryName;
    private string $description;
    private string $media;
    private string $createdAt;
    private string $updatedAt;
    private string $categoryStatus;
    private int $deleted;
    private int $parentCategoryId;

    /**
     * @return string
     */
    public function getCategoryId(): string
    {
        return $this->categoryId;
    }

    /**
     * @param string $categoryId
     * @return Category
     */
    public function setCategoryId(string $categoryId): self
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryName(): string
    {
        return $this->categoryName;
    }

    /**
     * @param string $categoryName
     * @return Category
     */
    public function setCategoryName(string $categoryName): self
    {
        $this->categoryName = $categoryName;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Category
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getMedia(): string
    {
        return $this->media;
    }

    /**
     * @param string $media
     * @return Category
     */
    public function setMedia(string $media): self
    {
        $this->media = $media;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    /**
     * @param string $createdAt
     * @return Category
     */
    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updatedAt;
    }

    /**
     * @param string $updatedAt
     * @return Category
     */
    public function setUpdatedAt(string $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategoryStatus(): string
    {
        return $this->categoryStatus;
    }

    /**
     * @param string $categoryStatus
     * @return Category
     */
    public function setCategoryStatus(string $categoryStatus): self
    {
        $this->categoryStatus = $categoryStatus;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeleted(): int
    {
        return $this->deleted;
    }

    /**
     * @param int $deleted
     * @return Category
     */
    public function setDeleted(int $deleted): self
    {
        $this->deleted = $deleted;
        return $this;
    }

    /**
     * @return int
     */
    public function getParentCategoryId(): int
    {
        return $this->parentCategoryId;
    }

    /**
     * @param int $parentCategoryId
     * @return Category
     */
    public function setParentCategoryId(int $parentCategoryId): self
    {
        $this->parentCategoryId = $parentCategoryId;
        return $this;
    }
}