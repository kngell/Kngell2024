<?php

declare(strict_types=1);

class FreeShippingCalculator implements ShippingCalculatorInterface
{
    private array $supportedClasses;

    public function __construct(array $supportedClasses = [])
    {
        $this->supportedClasses = $supportedClasses;
    }

    public function calculate(
        ?Weight $weight,
        ?Dimensions $dimensions,
        ?float $volumetricWeight = null,
        ?int $shippingClassId = null,
        string $destination = 'domestic',
    ): float {
        return 0.00;
    }

    public function supports(?int $shippingClassId): bool
    {
        if (empty($this->supportedClasses)) {
            return true;
        }

        return in_array($shippingClassId, $this->supportedClasses, true);
    }
}