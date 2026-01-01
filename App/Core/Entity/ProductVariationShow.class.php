<?php

declare(strict_types=1);

class ProductVariationShow extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    protected const array RELATIONSHIPS = [
        'stock_status' => StockStatus::class,
        'variation_attribute' => VariationAttribute::class,
        'variation_type' => VariationType::class,
    ];

    #[EntityFieldId()]
    private int $id;

    private string $sku;
    private string $name;
    private VariationType $variationType;

    /** @var VariationAttribute[] */
    private array $variationAttribute = [];

    private StockStatus $stockStatus;
    private int $stockQuantity;
    private string $status;
    private ?float $priceModifier = null;

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
     * @return VariationType
     */
    public function getVariationType(): VariationType
    {
        return $this->variationType;
    }

    /**
     * @param VariationType $variationType
     *
     * @return ProductVariationShow
     */
    public function setVariationType(VariationType $variationType): ProductVariationShow
    {
        $this->variationType = $variationType;

        return $this;
    }

    /**
     * @return VariationAttribute[]
     */
    public function getVariationAttribute(): array
    {
        return $this->variationAttribute;
    }

    // /**
    //  * @param VariationAttributes[]
    //  *
    //  * @return ProductVariationShow
    //  */
    // public function setVariationAttributes(array $variationAttributes): ProductVariationShow
    // {
    //     $this->variationAttributes = $variationAttributes;

    //     return $this;
    // }

    /**
     * @param VariationAttribute $variationAttribute
     *
     * @return ProductVariationShow
     */
    public function addVariationAttribute(VariationAttribute $variationAttribute): ProductVariationShow
    {
        $this->variationAttribute[] = $variationAttribute;

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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return ProductVariationShow
     */
    public function setStatus(string $status): ProductVariationShow
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getPriceModifier(): ?float
    {
        return $this->priceModifier;
    }

    /**
     * @param float|null $priceModifier
     *
     * @return ProductVariationShow
     */
    public function setPriceModifier(?float $priceModifier): ProductVariationShow
    {
        $this->priceModifier = $priceModifier;

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
     * @return ProductVariationShow
     */
    public function setId(int $id): ProductVariationShow
    {
        $this->id = $id;

        return $this;
    }
}