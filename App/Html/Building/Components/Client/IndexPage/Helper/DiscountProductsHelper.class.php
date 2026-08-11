<?php

declare(strict_types=1);

final class DiscountProductsHelper
{
    private const DEFAULT_LIMIT = 8;

    public function __construct(
        private readonly RegionContextInterface $regionContext,
    ) {
    }

    /**
     * @param ProductCardResponse[] $products
     * @param int|null $limit
     *
     * @return ProductCardResponse[]
     */
    public function getDiscountedProducts(array $products, ?int $limit = null): array
    {
        if (empty($products)) {
            return [];
        }

        $discounted = array_filter($products, function ($product) {
            return $this->isProductDiscounted($product);
        });

        usort($discounted, function ($a, $b) {
            return $this->getProductDiscountPercent($b) <=> $this->getProductDiscountPercent($a);
        });

        $limit = $limit ?? self::DEFAULT_LIMIT;

        return array_slice($discounted, 0, $limit);
    }

    private function isProductDiscounted(ProductCardResponse $response): bool
    {
        $product = $response->getEntity();

        if (!$product instanceof ProductCollection) {
            return false;
        }

        $regionalPrices = $product->getProductRegionalPrice();
        if (!is_array($regionalPrices)) {
            $regionalPrices = [$regionalPrices];
        }
        $regionCode = $this->regionContext->getRegionCode();

        foreach ($regionalPrices as $regionalPrice) {
            if ($regionalPrice->getRegionCode() !== strtolower($regionCode)) {
                continue;
            }

            if (!$regionalPrice->getIsOnSale()) {
                return false;
            }

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
            if (!$salePrice) {
                return false;
            }

            $basePrice = $regionalPrice->getBasePrice();
            if ($basePrice && $salePrice->isGreaterThanOrEqualTo($basePrice)) {
                return false;
            }

            return true;
        }

        return false;
    }

    private function getProductDiscountPercent(ProductCardResponse $response): int
    {
        $product = $response->getEntity();

        if (!$product instanceof Product) {
            return 0;
        }

        $regionalPrices = $product->getRegionalPrices();
        $regionCode = $this->regionContext->getRegionCode();

        foreach ($regionalPrices as $regionalPrice) {
            if ($regionalPrice->getRegionCode() !== $regionCode) {
                continue;
            }

            $discountPercent = $regionalPrice->getDiscountPercent();
            if ($discountPercent !== null) {
                return $discountPercent;
            }

            $salePrice = $regionalPrice->getSalePrice();
            if (!$salePrice) {
                return 0;
            }

            $basePrice = $regionalPrice->getBasePrice();
            if (!$basePrice) {
                $basePrice = $regionalPrice->getComparePrice();
            }

            if (!$basePrice) {
                return 0;
            }

            $baseAmount = (float) $basePrice->getAmount();
            $saleAmount = (float) $salePrice->getAmount();

            if ($baseAmount <= 0 || $baseAmount <= $saleAmount) {
                return 0;
            }

            return (int) round((($baseAmount - $saleAmount) / $baseAmount) * 100);
        }

        return 0;
    }
}