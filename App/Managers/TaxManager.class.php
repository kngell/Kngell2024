<?php

declare(strict_types=1);

use Brick\Math\Exception\MathException;
use Brick\Money\Context\AutoContext;
use Brick\Money\Money;

final class TaxManager
{
    private array $taxRates = [];
    private array $priceIncludesTaxRegions = [];

    public function __construct(
        private readonly RegionContextInterface $regionContext,
        private readonly MoneyManager $moneyManager,
        ?array $taxRates = null,
        ?array $priceIncludesTaxRegions = null,
    ) {
        $this->taxRates = $taxRates ?? [
            'EU' => '0.20',
            'GB' => '0.20',
            'FR' => '0.20',
            'DE' => '0.19',
            'IT' => '0.22',
            'ES' => '0.21',
            'US' => '0.0',
            'CA' => '0.05',
            'AU' => '0.10',
            'NZ' => '0.15',
            'JP' => '0.10',
            'SG' => '0.07',
        ];

        $this->priceIncludesTaxRegions = $priceIncludesTaxRegions ?? [
            'EU', 'GB', 'AU', 'NZ',
        ];
    }

    public function shouldPriceIncludeTax(): bool
    {
        $regionCode = $this->regionContext->getRegionCode();
        return in_array($regionCode, $this->priceIncludesTaxRegions);
    }

    public function getTaxRate(): string
    {
        $regionCode = $this->regionContext->getRegionCode();
        return $this->taxRates[$regionCode] ?? '0.0';
    }

    public function getTaxRateAsFloat(): float
    {
        return (float) $this->getTaxRate();
    }

    public function getTaxRateForRegion(string $regionCode): string
    {
        return $this->taxRates[$regionCode] ?? '0.0';
    }

    public function getTaxRateForRegionAsFloat(string $regionCode): float
    {
        return (float) $this->getTaxRateForRegion($regionCode);
    }

    public function addTax(Money $price, ?string $taxRate = null): Money
    {
        $rate = $taxRate ?? $this->getTaxRate();
        if ((float) $rate <= 0) {
            return $price;
        }

        try {
            $taxAmount = $price->multipliedBy($rate);
            return $price->plus($taxAmount);
        } catch (MathException $e) {
            throw new RuntimeException(
                sprintf('Error adding tax to price: %s', $this->moneyManager->format($price)),
                0,
                $e,
            );
        }
    }

    public function removeTax(Money $price, ?string $taxRate = null): Money
    {
        $rate = $taxRate ?? $this->getTaxRate();
        if ((float) $rate <= 0) {
            return $price;
        }

        try {
            $divisor = (string) (1 + (float) $rate);
            $amountBigDecimal = $price->getAmount()->dividedBy($divisor);

            return Money::of((string) $amountBigDecimal, $price->getCurrency(), new AutoContext());
        } catch (MathException $e) {
            throw new RuntimeException(
                sprintf('Error removing tax from price: %s', $this->moneyManager->format($price)),
                0,
                $e,
            );
        }
    }

    public function calculateTax(Money $price, ?string $taxRate = null): Money
    {
        $rate = $taxRate ?? $this->getTaxRate();
        if ((float) $rate <= 0) {
            return $this->moneyManager->zero($price->getCurrency()->getCurrencyCode());
        }

        try {
            return $price->multipliedBy($rate);
        } catch (MathException $e) {
            throw new RuntimeException(
                sprintf('Error calculating tax for price: %s', $this->moneyManager->format($price)),
                0,
                $e,
            );
        }
    }

    public function getTaxAmountFormatted(Money $price, ?string $taxRate = null): string
    {
        $taxAmount = $this->calculateTax($price, $taxRate);
        return $this->moneyManager->format($taxAmount);
    }

    public function getPriceWithTaxFormatted(Money $price, ?string $taxRate = null): string
    {
        $priceWithTax = $this->addTax($price, $taxRate);
        return $this->moneyManager->format($priceWithTax);
    }

    public function setTaxRate(string $regionCode, string $rate): self
    {
        $this->taxRates[$regionCode] = $rate;
        return $this;
    }

    public function setTaxRateFromFloat(string $regionCode, float $rate): self
    {
        $this->taxRates[$regionCode] = $this->floatToString($rate);
        return $this;
    }

    public function setPriceIncludesTax(string $regionCode, bool $includesTax): self
    {
        if ($includesTax && !in_array($regionCode, $this->priceIncludesTaxRegions)) {
            $this->priceIncludesTaxRegions[] = $regionCode;
        } elseif (!$includesTax) {
            $this->priceIncludesTaxRegions = array_filter(
                $this->priceIncludesTaxRegions,
                fn ($code) => $code !== $regionCode,
            );
        }
        return $this;
    }

    public function getTaxRates(): array
    {
        return $this->taxRates;
    }

    public function getTaxRatesAsFloat(): array
    {
        return array_map('floatval', $this->taxRates);
    }

    public function getPriceIncludesTaxRegions(): array
    {
        return $this->priceIncludesTaxRegions;
    }

    private function floatToString(float $value): string
    {
        $string = (string) $value;

        if (strpos($string, 'E') !== false || strpos($string, 'e') !== false) {
            $string = number_format($value, 10, '.', '');
        }
        return rtrim(rtrim($string, '0'), '.');
    }
}