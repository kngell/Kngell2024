<?php

declare(strict_types=1);

enum CategorySection: string
{
    public static function getKey(): string
    {
        return 'category_sections';
    }
    case BASIC_INFOS = 'basics_information';
    case CANONICAL_INFOS = 'canonicals_information';
    case CONTENT_AREA = 'content_area';
    case CONTENT_STYLE = 'content_style';
    case MEDIA = 'media';
    case NAVIGATION_INFOS = 'navigation_infos';
    case OG_MEDIA = 'og_media';
    case OPEN_GRAPH = 'open_graph';
    case PRICE_RANGE = 'price_ranges';
    case SOCIAL_MEDIA = 'social_media';
}