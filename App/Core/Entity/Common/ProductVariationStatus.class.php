<?php

declare(strict_types=1);

enum ProductVariationStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}