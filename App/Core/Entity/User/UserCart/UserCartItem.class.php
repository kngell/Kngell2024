<?php

declare(strict_types=1);

class UserCartItem extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[DisplayFormat(
        obfuscate: true,
        obfuscationStrategy: 'hashid',
        nullPlaceholder: 'No ID',
    )]
    #[EntityFieldId(name: 'cart_item_id', type: FieldType::INT)]
    private int $id;

    private int $cartId;
    private int $productId;
    private int $quantity = 1;
    private ?array $variantData = null;

    /**
     * @return int
     */
    public function getCartId(): int
    {
        return $this->cartId;
    }

    /**
     * @param int $cartId
     *
     * @return UserCartItem
     */
    public function setCartId(int $cartId): UserCartItem
    {
        $this->cartId = $cartId;

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
     * @return UserCartItem
     */
    public function setProductId(int $productId): UserCartItem
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return UserCartItem
     */
    public function setQuantity(int $quantity): UserCartItem
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return null|array
     */
    public function getVariantData(): ?array
    {
        return $this->variantData;
    }

    /**
     * @param null|array $variantData
     *
     * @return UserCartItem
     */
    public function setVariantData(?array $variantData): UserCartItem
    {
        $this->variantData = $variantData;

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
     * @return UserCartItem
     */
    public function setId(int $id): UserCartItem
    {
        $this->id = $id;

        return $this;
    }
}