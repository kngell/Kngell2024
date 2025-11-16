<?php

declare(strict_types=1);

class FlatRateShippingCalculator implements ShippingCalculatorInterface
{
    private float $flatRate;
    private array $supportedClasses;

    public function __construct(float $flatRate = 5.99, array $supportedClasses = [])
    {
        $this->flatRate = $flatRate;
        $this->supportedClasses = $supportedClasses;
    }

    public function calculate(
        ?Weight $weight,
        ?Dimensions $dimensions,
        ?float $volumetricWeight = null,
        ?int $shippingClassId = null,
        string $destination = 'domestic',
    ): float {
        return $this->flatRate;
    }

    public function supports(?int $shippingClassId): bool
    {
        if (empty($this->supportedClasses)) {
            return true;
        }

        return in_array($shippingClassId, $this->supportedClasses, true);
    }
}