<?php

declare(strict_types=1);

use Brick\Money\Money;

class ProductRegionalPrice extends Entity implements TimestampableInterface
{
    use EntityTimestampableTrait;

    protected const array RELATIONSHIPS = [
        'product' => Product::class,
        'region' => Region::class,
        'currency' => Currency::class,
    ];

    #[EntityFieldId(name: 'price_id')]
    private int $id;

    // Renamed for better convention and clarity
    private int $productId;
    private string $regionCode;
    private int $currencyId;

    // 💰 CRITICAL: Use Money Value Object for precision
    private Money $basePrice;
    private ?Money $comparePrice = null;
    private ?Money $costPrice = null;
    private ?Money $salePrice = null;

    // Maps to the new column in the SQL schema
    private bool $priceIncludesTax = false;
    private bool $isActive = true;

    // Relationships
    private ?Product $product = null;
    private ?Region $region = null;
    private ?Currency $currency = null;

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
     * @return ProductRegionalPrice
     */
    public function setId(int $id): ProductRegionalPrice
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
     * @return ProductRegionalPrice
     */
    public function setProductId(int $productId): ProductRegionalPrice
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * @return string
     */
    public function getRegionCode(): string
    {
        return $this->regionCode;
    }

    /**
     * @param string $regionCode
     *
     * @return ProductRegionalPrice
     */
    public function setRegionCode(string $regionCode): ProductRegionalPrice
    {
        $this->regionCode = $regionCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrencyId(): int
    {
        return $this->currencyId;
    }

    /**
     * @param int $currencyId
     *
     * @return ProductRegionalPrice
     */
    public function setCurrencyId(int $currencyId): ProductRegionalPrice
    {
        $this->currencyId = $currencyId;

        return $this;
    }

    /**
     * @return Money
     */
    public function getBasePrice(): Money
    {
        return $this->basePrice;
    }

    /**
     * @param Money $basePrice
     *
     * @return ProductRegionalPrice
     */
    public function setBasePrice(Money $basePrice): ProductRegionalPrice
    {
        $this->basePrice = $basePrice;

        return $this;
    }

    /**
     * @return null|Money
     */
    public function getComparePrice(): ?Money
    {
        return $this->comparePrice;
    }

    /**
     * @param null|Money $comparePrice
     *
     * @return ProductRegionalPrice
     */
    public function setComparePrice(?Money $comparePrice): ProductRegionalPrice
    {
        $this->comparePrice = $comparePrice;

        return $this;
    }

    /**
     * @return null|Money
     */
    public function getCostPrice(): ?Money
    {
        return $this->costPrice;
    }

    /**
     * @param null|Money $costPrice
     *
     * @return ProductRegionalPrice
     */
    public function setCostPrice(?Money $costPrice): ProductRegionalPrice
    {
        $this->costPrice = $costPrice;

        return $this;
    }

    /**
     * @return null|Money
     */
    public function getSalePrice(): ?Money
    {
        return $this->salePrice;
    }

    /**
     * @param null|Money $salePrice
     *
     * @return ProductRegionalPrice
     */
    public function setSalePrice(?Money $salePrice): ProductRegionalPrice
    {
        $this->salePrice = $salePrice;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPriceIncludesTax(): bool
    {
        return $this->priceIncludesTax;
    }

    /**
     * @param bool $priceIncludesTax
     *
     * @return ProductRegionalPrice
     */
    public function setPriceIncludesTax(bool $priceIncludesTax): ProductRegionalPrice
    {
        $this->priceIncludesTax = $priceIncludesTax;

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
     * @return ProductRegionalPrice
     */
    public function setIsActive(bool $isActive): ProductRegionalPrice
    {
        $this->isActive = $isActive;

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
     * @return ProductRegionalPrice
     */
    public function setProduct(?Product $product): ProductRegionalPrice
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return null|Region
     */
    public function getRegion(): ?Region
    {
        return $this->region;
    }

    /**
     * @param null|Region $region
     *
     * @return ProductRegionalPrice
     */
    public function setRegion(?Region $region): ProductRegionalPrice
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return null|Currency
     */
    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    /**
     * @param null|Currency $currency
     *
     * @return ProductRegionalPrice
     */
    public function setCurrency(?Currency $currency): ProductRegionalPrice
    {
        $this->currency = $currency;

        return $this;
    }

    protected function getRelationShip(string $name): string
    {
        return static::RELATIONSHIPS[$name];
    }
}