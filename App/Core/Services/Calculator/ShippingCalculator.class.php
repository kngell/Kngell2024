<?php

declare(strict_types=1);

class ShippingCalculator implements ShippingCalculatorInterface
{
    private array $shippingRates;
    private array $shippingClasses;
    private float $defaultRate;
    private float $volumetricFactor;

    public function __construct(
        array $shippingRates = [],
        array $shippingClasses = [],
        float $defaultRate = 5.00,
        float $volumetricFactor = 5000,
    ) {
        $this->shippingRates = $shippingRates;
        $this->shippingClasses = $shippingClasses;
        $this->defaultRate = $defaultRate;
        $this->volumetricFactor = $volumetricFactor;
    }

    public function calculate(
        ?Weight $weight,
        ?Dimensions $dimensions,
        ?float $volumetricWeight = null,
        ?int $shippingClassId = null,
        string $destination = 'domestic',
    ): float {
        // If product doesn't require shipping
        if ($weight === null && $dimensions === null) {
            return 0.00;
        }

        // Calculate chargeable weight (actual vs volumetric)
        $chargeableWeight = $this->calculateChargeableWeight(
            $weight,
            $dimensions,
            $volumetricWeight,
        );

        // Get base rate based on weight/dimensions
        $baseRate = $this->calculateBaseRate($chargeableWeight, $destination);

        // Apply shipping class multiplier if applicable
        $finalRate = $this->applyShippingClassMultiplier($baseRate, $shippingClassId);

        return max(0.00, $finalRate);
    }

    public function supports(?int $shippingClassId): bool
    {
        // This calculator supports all shipping classes
        return true;
    }

    // Configuration methods
    public function setShippingRates(array $rates): self
    {
        $this->shippingRates = $rates;
        return $this;
    }

    public function setShippingClasses(array $classes): self
    {
        $this->shippingClasses = $classes;
        return $this;
    }

    public function setDefaultRate(float $rate): self
    {
        $this->defaultRate = $rate;
        return $this;
    }

    private function calculateChargeableWeight(
        ?Weight $weight,
        ?Dimensions $dimensions,
        ?float $volumetricWeight = null,
    ): float {
        $actualWeight = $weight?->getValue() ?? 0.0;

        // Calculate volumetric weight if not provided
        if ($volumetricWeight === null && $dimensions !== null) {
            $volumetricWeight = $dimensions->getVolumetricWeight($this->volumetricFactor);
        }

        // Use the greater of actual weight or volumetric weight
        if ($volumetricWeight !== null && $volumetricWeight > $actualWeight) {
            return $volumetricWeight;
        }

        return $actualWeight;
    }

    private function calculateBaseRate(float $chargeableWeight, string $destination): float
    {
        $rates = $this->shippingRates[$destination] ?? $this->shippingRates['domestic'] ?? [];

        // Find the appropriate rate bracket
        foreach ($rates as $bracket) {
            $maxWeight = $bracket['max_weight'] ?? PHP_FLOAT_MAX;
            $minWeight = $bracket['min_weight'] ?? 0.0;

            if ($chargeableWeight >= $minWeight && $chargeableWeight <= $maxWeight) {
                return $bracket['rate'] ?? $this->defaultRate;
            }
        }

        // Calculate rate for weights beyond brackets
        return $this->calculateOversizedRate($chargeableWeight, $destination);
    }

    private function calculateOversizedRate(float $weight, string $destination): float
    {
        $baseRate = $this->shippingRates[$destination]['base_rate'] ?? $this->defaultRate;
        $perKgRate = $this->shippingRates[$destination]['per_kg_rate'] ?? 2.00;

        return $baseRate + (max(0, $weight - 1) * $perKgRate);
    }

    private function applyShippingClassMultiplier(float $baseRate, ?int $shippingClassId): float
    {
        if ($shippingClassId === null) {
            return $baseRate;
        }

        $multiplier = $this->shippingClasses[$shippingClassId]['multiplier'] ?? 1.0;
        return $baseRate * $multiplier;
    }
}