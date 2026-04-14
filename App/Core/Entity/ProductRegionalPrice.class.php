<?php

declare(strict_types=1);

use Brick\Money\Money;

class ProductRegionalPrice extends Entity implements TimestampableInterface, SoftDeletableInterface
{
    use EntityTimestampableTrait;
    use SoftDeletableTrait;

    #[EntityFieldId(name: 'price_id')]
    private int $priceId;

    private int $productId;
    private string $regionCode;
    private int $currencyId;
    private ?Money $basePrice;
    private ?Money $comparePrice;
    private ?Money $costPrice;
    private ?Money $salePrice;
    private bool $priceIncludesTax = false;
    private ?DateTimeImmutable $saleStartDate;
    private ?DateTimeImmutable $saleEndDate;
    private ?bool $isActive;

    public function isCurrentlyOnSale(): bool
    {
        if ($this->salePrice === null || !$this->isActive) {
            return false;
        }

        $now = new DateTimeImmutable();
        $startMatch = $this->saleStartDate === null || $now >= $this->saleStartDate;
        $endMatch = $this->saleEndDate === null || $now <= $this->saleEndDate;

        return $startMatch && $endMatch;
    }

    /**
     * @return int
     */
    public function getPriceId(): int
    {
        return $this->priceId;
    }

    /**
     * @param int $priceId
     *
     * @return ProductRegionalPrice
     */
    public function setPriceId(int $priceId): ProductRegionalPrice
    {
        $this->priceId = $priceId;

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
     * @return null|Money
     */
    public function getBasePrice(): ?Money
    {
        return $this->basePrice;
    }

    /**
     * @param null|Money $basePrice
     *
     * @return ProductRegionalPrice
     */
    public function setBasePrice(?Money $basePrice): ProductRegionalPrice
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
     * @return null|DateTimeImmutable
     */
    public function getSaleStartDate(): ?DateTimeImmutable
    {
        return $this->saleStartDate;
    }

    /**
     * @param null|DateTimeImmutable $saleStartDate
     *
     * @return ProductRegionalPrice
     */
    public function setSaleStartDate(?DateTimeImmutable $saleStartDate): ProductRegionalPrice
    {
        $this->saleStartDate = $saleStartDate;

        return $this;
    }

    /**
     * @return null|DateTimeImmutable
     */
    public function getSaleEndDate(): ?DateTimeImmutable
    {
        return $this->saleEndDate;
    }

    /**
     * @param null|DateTimeImmutable $saleEndDate
     *
     * @return ProductRegionalPrice
     */
    public function setSaleEndDate(?DateTimeImmutable $saleEndDate): ProductRegionalPrice
    {
        $this->saleEndDate = $saleEndDate;

        return $this;
    }

    /**
     * @return null|bool
     */
    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    /**
     * @param null|bool $isActive
     *
     * @return ProductRegionalPrice
     */
    public function setIsActive(?bool $isActive): ProductRegionalPrice
    {
        $this->isActive = $isActive;

        return $this;
    }
}
