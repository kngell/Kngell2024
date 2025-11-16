<?php

declare(strict_types=1);

class ShippingCalculatorFactory
{
    private array $calculators;

    public function __construct()
    {
        $this->registerDefaultCalculators();
    }

    public function createForProduct(Product $product): ShippingCalculatorInterface
    {
        // Check if product has free shipping
        if ($product->getShippingClassId() === 1) { // Assuming 1 = free shipping
            return new FreeShippingCalculator();
        }

        // Check for flat rate shipping classes
        $flatRateClasses = [2, 3]; // Example flat rate class IDs
        if (in_array($product->getShippingClassId(), $flatRateClasses, true)) {
            return new FlatRateShippingCalculator(7.99, $flatRateClasses);
        }

        // Default to standard calculator
        return $this->createStandardCalculator();
    }

    public function createStandardCalculator(): ShippingCalculatorInterface
    {
        $rates = [
            'domestic' => [
                ['max_weight' => 0.5, 'rate' => 3.99],
                ['max_weight' => 1.0, 'rate' => 4.99],
                ['max_weight' => 2.0, 'rate' => 6.99],
                ['max_weight' => 5.0, 'rate' => 9.99],
                ['base_rate' => 9.99, 'per_kg_rate' => 1.50],
            ],
            'international' => [
                ['max_weight' => 0.5, 'rate' => 12.99],
                ['max_weight' => 1.0, 'rate' => 19.99],
                ['max_weight' => 2.0, 'rate' => 29.99],
                ['base_rate' => 29.99, 'per_kg_rate' => 8.00],
            ],
        ];

        $shippingClasses = [
            4 => ['multiplier' => 1.2], // Fragile items +20%
            5 => ['multiplier' => 1.5], // Heavy items +50%
        ];

        return new ShippingCalculator($rates, $shippingClasses);
    }

    public function getCalculator(string $type): ShippingCalculatorInterface
    {
        if (!isset($this->calculators[$type])) {
            throw new InvalidArgumentException("Unknown calculator type: {$type}");
        }

        return $this->calculators[$type]();
    }

    private function registerDefaultCalculators(): void
    {
        $this->calculators = [
            'standard' => fn () => $this->createStandardCalculator(),
            'flat_rate' => fn () => new FlatRateShippingCalculator(),
            'free' => fn () => new FreeShippingCalculator(),
        ];
    }
}