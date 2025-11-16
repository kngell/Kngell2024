<?php

declare(strict_types=1);

class Cart extends Entity
{
    #[EntityFieldId(name: 'cart_id')]
    private string $cartId;
    private ?int $userId;
    private ?string $itemId;
    private ?int $itemQuantity;
    private ?string $itemName;
    private ?string $itemPrice;
    private ?string $media;
    private ?string $categoryName;
    private ?string $brandName;
    private ?int $numberOfItems;
    private ?string $description;
    private ?string $itemSku;

    /**
     * @return string
     */
    public function getCartId(): string
    {
        return $this->cartId;
    }

    /**
     * @param string $cartId
     * @return Cart
     */
    public function setCartId(string $cartId): self
    {
        $this->cartId = $cartId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getUserId(): ?int
    {
        return $this->userId ?? null;
    }

    /**
     * @param null|int $userId
     * @return Cart
     */
    public function setUserId(?int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getItemId(): ?string
    {
        return $this->itemId;
    }

    /**
     * @param null|string $itemId
     * @return Cart
     */
    public function setItemId(?string $itemId): self
    {
        $this->itemId = $itemId;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getItemQuantity(): ?int
    {
        return $this->itemQuantity ?? null;
    }

    /**
     * @param null|int $itemQuantity
     * @return Cart
     */
    public function setItemQuantity(?int $itemQuantity): self
    {
        $this->itemQuantity = $itemQuantity;

        return $this;
    }

    /**
     * Get the value of itemName.
     */
    public function getItemName(): ?string
    {
        return $this->itemName ?? null;
    }

    /**
     * Set the value of itemName.
     */
    public function setItemName(?string $itemName): self
    {
        $this->itemName = $itemName;

        return $this;
    }

    /**
     * Get the value of itemPrice.
     */
    public function getItemPrice(): ?string
    {
        return $this->itemPrice ?? null;
    }

    /**
     * Set the value of itemPrice.
     */
    public function setItemPrice(?string $itemPrice): self
    {
        $this->itemPrice = $itemPrice;

        return $this;
    }

    /**
     * Get the value of numberOfItems.
     */
    public function getNumberOfItems(): ?int
    {
        return $this->numberOfItems ?? null;
    }

    /**
     * Set the value of numberOfItems.
     */
    public function setNumberOfItems(?int $numberOfItems): self
    {
        $this->numberOfItems = $numberOfItems;

        return $this;
    }

    /**
     * Get the value of media.
     */
    public function getMedia(): ?string
    {
        return $this->media ?? null;
    }

    /**
     * Set the value of media.
     */
    public function setMedia(?string $media): self
    {
        $this->media = $media;

        return $this;
    }

    /**
     * Get the value of categoryName.
     */
    public function getCategoryName(): ?string
    {
        return $this->categoryName ?? null;
    }

    /**
     * Set the value of categoryName.
     */
    public function setCategoryName(?string $categoryName): self
    {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * Get the value of brandName.
     */
    public function getBrandName(): ?string
    {
        return $this->brandName ?? null;
    }

    /**
     * Set the value of brandName.
     */
    public function setBrandName(?string $brandName): self
    {
        $this->brandName = $brandName;

        return $this;
    }

    /**
     * Get the value of description.
     */
    public function getDescription(): ?string
    {
        return $this->description ?? null;
    }

    /**
     * Set the value of description.
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of itemSku.
     */
    public function getItemSku(): ?string
    {
        return $this->itemSku ?? null;
    }

    /**
     * Set the value of itemSku.
     */
    public function setItemSku(?string $itemSku): self
    {
        $this->itemSku = $itemSku;

        return $this;
    }
}