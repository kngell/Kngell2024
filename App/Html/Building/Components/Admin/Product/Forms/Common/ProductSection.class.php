<?php

declare(strict_types=1);
enum ProductSection: string
{
    case GENERAL_INFOS = 'general-information';
    case MEDIA = 'media';
    case PRICING = 'pricing';
    case INVENTORY = 'inventory';
    case VARIATION = 'variation';
    case SHIPPING = 'shipping';
    case BRAND = 'brand';
    case CATEGORY = 'category';
    case PRODUCT_STATUS = 'product-status';
    case PRODUCT_TAGS = 'product_tags';
}