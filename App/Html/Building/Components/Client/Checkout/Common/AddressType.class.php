<?php

declare(strict_types=1);

enum AddressType: string
{
    public function isShippingAllowed(): bool
    {
        return $this === self::BOTH || $this === self::SHIPPING;
    }

    public function isBillingAllowed(): bool
    {
        return $this === self::BOTH || $this === self::BILLING;
    }

    public function getLabel(): string
    {
        return match($this) {
            self::BOTH => 'Shipping & Billing',
            self::SHIPPING => 'Shipping Only',
            self::BILLING => 'Billing Only',
        };
    }
    case BOTH = 'both';
    case SHIPPING = 'shipping';
    case BILLING = 'billing';
}