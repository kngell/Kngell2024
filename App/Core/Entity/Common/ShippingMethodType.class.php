<?php

declare(strict_types=1);

enum ShippingMethodType: string
{
    case FLAT_RATE = 'flat_rate';
    case FREE = 'free';
    case WEIGHT_BASED = 'weight_based';
    case PRICE_BASED = 'price_based';
    case ZONE_BASED = 'zone_based';
    case API_BASED = 'api_based';
}