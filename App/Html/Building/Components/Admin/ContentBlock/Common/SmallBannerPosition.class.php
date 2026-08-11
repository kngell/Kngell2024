<?php

declare(strict_types=1);

enum SmallBannerPosition: string
{
    use BannerPositionTrait;

    private const WIDTH_CONFIG = [
        'banner-left__wide' => ['mobile' => 400, 'tablet' => 600, 'desktop' => 800],
        'banner-square@light' => ['mobile' => 200, 'tablet' => 300, 'desktop' => 400],
        'banner-square@dark' => ['mobile' => 200, 'tablet' => 300, 'desktop' => 400],
        'banner-right' => ['mobile' => 300, 'tablet' => 450, 'desktop' => 600],
    ];

    public static function getAllValues(): array
    {
        return [
            self::LEFT_WIDE->value,
            self::LEFT_SQUARE_DARK->value,
            self::LEFT_SQUARE_LIGHT->value,
            self::RIGHT->value,
        ];
    }

    protected static function getWidthConfig(): array
    {
        return self::WIDTH_CONFIG;
    }

    case LEFT_WIDE = 'banner-left__wide';
    case LEFT_SQUARE_LIGHT = 'banner-square@light';
    case LEFT_SQUARE_DARK = 'banner-square@dark';
    case RIGHT = 'banner-right';
}