<?php

declare(strict_types=1);

interface ShippingCalculatorInterface
{
    public function calculate(
        ?Weight $weight,
        ?Dimensions $dimensions,
        ?float $volumetricWeight = null,
        ?int $shippingClassId = null,
        string $destination = 'domestic',
    ): float;

    public function supports(?int $shippingClassId): bool;
}