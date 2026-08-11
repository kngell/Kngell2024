<?php

declare(strict_types=1);
/**
 * @method ProductCollection getEntity()
 * @method ?string show(Entity $entity, string $field)
 */
trait ProductPriceTrait
{
    public function getBasePrice(): ?string
    {
        $regionalPrice = $this->getRegionalPrice();
        if (!$regionalPrice) {
            return null;
        }
        return $this->show($regionalPrice, 'base_price');
    }

    public function getComparePrice(): ?string
    {
        $regionalPrice = $this->getRegionalPrice();
        if (!$regionalPrice) {
            return null;
        }
        return $this->show($regionalPrice, 'compare_price');
    }

    public function getSalePrice(): ?string
    {
        $regionalPrice = $this->getRegionalPrice();
        if (!$regionalPrice) {
            return null;
        }
        return $this->show($regionalPrice, 'sale_price');
    }

    public function getCostPrice(): ?string
    {
        $regionalPrice = $this->getRegionalPrice();
        if (!$regionalPrice) {
            return null;
        }
        return $this->show($regionalPrice, 'cost_price');
    }

    public function getCurrentPrice(): ?string
    {
        $regionalPrice = $this->getRegionalPrice();
        if (!$regionalPrice) {
            return null;
        }

        if ($this->isOnSale()) {
            $salePrice = $regionalPrice->getSalePrice();
            if ($salePrice) {
                return $this->show($regionalPrice, 'sale_price');
            }
        }

        return $this->show($regionalPrice, 'base_price');
    }

    public function isOnSale(): bool
    {
        $regionalPrice = $this->getRegionalPrice();
        if (!$regionalPrice) {
            return false;
        }

        // Check isOnSale flag
        if (!$regionalPrice->getIsOnSale()) {
            return false;
        }

        // Check date range
        $now = new DateTimeImmutable();
        $saleStart = $regionalPrice->getSaleStartDate();
        $saleEnd = $regionalPrice->getSaleEndDate();

        if ($saleStart && $now < $saleStart) {
            return false;
        }
        if ($saleEnd && $now > $saleEnd) {
            return false;
        }

        $salePrice = $regionalPrice->getSalePrice();
        $basePrice = $regionalPrice->getBasePrice();

        if (!$salePrice) {
            return false;
        }

        if ($basePrice && $salePrice->isGreaterThanOrEqualTo($basePrice)) {
            return false;
        }

        return true;
    }

    public function getDiscountPercent(): ?int
    {
        $regionalPrice = $this->getRegionalPrice();
        if (!$regionalPrice) {
            return null;
        }

        if (!$this->isOnSale()) {
            return null;
        }

        $discountPercent = $regionalPrice->getDiscountPercent();
        if ($discountPercent !== null) {
            return $discountPercent;
        }

        $basePrice = $regionalPrice->getBasePrice();
        $salePrice = $regionalPrice->getSalePrice();

        if (!$basePrice || !$salePrice) {
            return null;
        }

        $baseAmount = $basePrice->getAmount();
        $saleAmount = $salePrice->getAmount();

        if ($baseAmount->isLessThanOrEqualTo(0) || $baseAmount->isLessThanOrEqualTo($saleAmount)) {
            return null;
        }

        $discount = $baseAmount->minus($saleAmount)
            ->dividedBy($baseAmount, 4)
            ->multipliedBy(100)
            ->toFloat();

        return (int) round($discount);
    }

    public function isActive(): bool
    {
        $regionalPrice = $this->getRegionalPrice();
        return $regionalPrice?->getIsActive() ?? false;
    }

    public function includesTax(): bool
    {
        $regionalPrice = $this->getRegionalPrice();
        return $regionalPrice?->getPriceIncludesTax() ?? false;
    }

    public function getRegionCode(): ?string
    {
        $regionalPrice = $this->getRegionalPrice();
        return $regionalPrice?->getRegionCode();
    }

    public function getCurrencyId(): ?int
    {
        $regionalPrice = $this->getRegionalPrice();
        return $regionalPrice?->getCurrencyId();
    }

    public function getSaleStartDate(): ?DateTimeImmutable
    {
        $regionalPrice = $this->getRegionalPrice();
        return $regionalPrice?->getSaleStartDate();
    }

    public function getSaleEndDate(): ?DateTimeImmutable
    {
        $regionalPrice = $this->getRegionalPrice();
        return $regionalPrice?->getSaleEndDate();
    }

    public function getPriceData(): array
    {
        return [
            'basePrice' => $this->getBasePrice(),
            'comparePrice' => $this->getComparePrice(),
            'salePrice' => $this->getSalePrice(),
            'costPrice' => $this->getCostPrice(),
            'currentPrice' => $this->getCurrentPrice(),
            'onSale' => $this->isOnSale(),
            'isActive' => $this->isActive(),
            'includesTax' => $this->includesTax(),
            'discountPercent' => $this->getDiscountPercent(),
            'regionCode' => $this->getRegionCode(),
            'currencyId' => $this->getCurrencyId(),
            'saleStartDate' => $this->getSaleStartDate()?->format('Y-m-d H:i:s'),
            'saleEndDate' => $this->getSaleEndDate()?->format('Y-m-d H:i:s'),
        ];
    }

    private function getRegionalPrice(): ?ProductRegionalPrice
    {
        $product = $this->getEntity();
        if (!$product) {
            return null;
        }
        return $product->getProductRegionalPrice();
    }
}