<?php

declare(strict_types=1);

enum IndexPageSection: string
{
    case HERO = 'heroSection';
    case SMALL_BANNER = 'smallBannerSection';
    case CATEGORY = 'categorySection';
    case PRODUCT = 'productSection';
    case BIG_BANNER = 'bigBannerSection';
    case SUMMER_BANNER = 'summerBannerSection';
    case DISCOUNT = 'discountSection';
}