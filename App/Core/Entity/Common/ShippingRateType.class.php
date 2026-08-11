<?php

declare(strict_types=1);

enum ShippingRateType: string
{
    case FIXED = 'fixed';
    case PERCENTAGE = 'percentage';
    case FREE = 'free';
}