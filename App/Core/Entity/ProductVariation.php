<?php

declare(strict_types=1);

use Brick\Money\Money;

class ProductVariation extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    #[EntityFieldId()]
    private int $id; //Unique product identifier

    private int $productId;
    private int $variationTypeId;
    private string $name;
    private string $sku;
    private Money $priceModifier;
    private int $stockQuantity = 0;
    private int $stockStatusId;
    private ProductVariationStatus $status;

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
     * @return ProductVariation
     */
    public function setId(int $id): ProductVariation
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
     * @return ProductVariation
     */
    public function setProductId(int $productId): ProductVariation
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return int
     */
    public function getVariationTypeId(): int
    {
        return $this->variationTypeId;
    }

    /**
     * @param int $variationTypeId
     *
     * @return ProductVariation
     */
    public function setVariationTypeId(int $variationTypeId): ProductVariation
    {
        $this->variationTypeId = $variationTypeId;

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
     * @return ProductVariation
     */
    public function setName(string $name): ProductVariation
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @param string $sku
     *
     * @return ProductVariation
     */
    public function setSku(string $sku): ProductVariation
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * @return Money
     */
    public function getPriceModifier(): Money
    {
        return $this->priceModifier;
    }

    /**
     * @param Money $priceModifier
     *
     * @return ProductVariation
     */
    public function setPriceModifier(Money $priceModifier): ProductVariation
    {
        $this->priceModifier = $priceModifier;

        return $this;
    }

    /**
     * @return int
     */
    public function getStockQuantity(): int
    {
        return $this->stockQuantity;
    }

    /**
     * @param int $stockQuantity
     *
     * @return ProductVariation
     */
    public function setStockQuantity(int $stockQuantity): ProductVariation
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    /**
     * @return int
     */
    public function getStockStatusId(): int
    {
        return $this->stockStatusId;
    }

    /**
     * @param int $stockStatusId
     *
     * @return ProductVariation
     */
    public function setStockStatusId(int $stockStatusId): ProductVariation
    {
        $this->stockStatusId = $stockStatusId;

        return $this;
    }

    /**
     * @return ProductVariationStatus
     */
    public function getStatus(): ProductVariationStatus
    {
        return $this->status;
    }

    /**
     * @param ProductVariationStatus $status
     *
     * @return ProductVariation
     */
    public function setStatus(ProductVariationStatus $status): ProductVariation
    {
        $this->status = $status;

        return $this;
    }
}