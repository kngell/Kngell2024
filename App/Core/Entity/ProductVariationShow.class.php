<?php

declare(strict_types=1);
class ProductVariationShow extends Entity
{
    private string $sku;
    private string $name;

    /** @var VariationAttribute[] */
    private array $attributes;

    private StockStatus $stockStatus; // StockStatus entity
    private int $stockQuantity;
    private bool $allowBackOrders;

    /** @var ProductRegionalPrice[] */
    private array $regionalPrices = [];

    private ?string $image = null;

    public function isAvailable(): bool
    {
        $stockStatusCode = $this->stockStatus->getStockStatusCode();

        return $stockStatusCode === StockStatusCode::IN_STOCK
            || ($stockStatusCode === StockStatusCode::BACKORDERED && $this->allowBackOrders);
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
     * @return ProductVariationShow
     */
    public function setSku(string $sku): ProductVariationShow
    {
        $this->sku = $sku;

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
     * @return ProductVariationShow
     */
    public function setName(string $name): ProductVariationShow
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     *
     * @return ProductVariationShow
     */
    public function setAttributes(array $attributes): ProductVariationShow
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @return StockStatus
     */
    public function getStockStatus(): StockStatus
    {
        return $this->stockStatus;
    }

    /**
     * @param StockStatus $stockStatus
     *
     * @return ProductVariationShow
     */
    public function setStockStatus(StockStatus $stockStatus): ProductVariationShow
    {
        $this->stockStatus = $stockStatus;

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
     * @return ProductVariationShow
     */
    public function setStockQuantity(int $stockQuantity): ProductVariationShow
    {
        $this->stockQuantity = $stockQuantity;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowBackOrders(): bool
    {
        return $this->allowBackOrders;
    }

    /**
     * @param bool $allowBackOrders
     *
     * @return ProductVariationShow
     */
    public function setAllowBackOrders(bool $allowBackOrders): ProductVariationShow
    {
        $this->allowBackOrders = $allowBackOrders;

        return $this;
    }

    /**
     * @return array
     */
    public function getRegionalPrices(): array
    {
        return $this->regionalPrices;
    }

    /**
     * @param array $regionalPrices
     *
     * @return ProductVariationShow
     */
    public function setRegionalPrices(array $regionalPrices): ProductVariationShow
    {
        $this->regionalPrices = $regionalPrices;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param null|string $image
     *
     * @return ProductVariationShow
     */
    public function setImage(?string $image): ProductVariationShow
    {
        $this->image = $image;

        return $this;
    }
}