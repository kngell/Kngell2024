<?php

declare(strict_types=1);
class ProductImageGallery extends Entity
{
    #[EntityFieldId()]
    private int $id;

    private int $productId;
    private string $imageUrl;
    private string $altText;
    private int $sortOrder = 0;

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
     * @return ProductImageGallery
     */
    public function setId(int $id): ProductImageGallery
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     *
     * @return ProductImageGallery
     */
    public function setProductId(int $productId): ProductImageGallery
    {
        $this->productId = $productId;

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
     * @return ProductImageGallery
     */
    public function setImageUrl(string $imageUrl): ProductImageGallery
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getAltText(): string
    {
        return $this->altText;
    }

    /**
     * @param string $altText
     *
     * @return ProductImageGallery
     */
    public function setAltText(string $altText): ProductImageGallery
    {
        $this->altText = $altText;

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
     * @return ProductImageGallery
     */
    public function setSortOrder(int $sortOrder): ProductImageGallery
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }
}