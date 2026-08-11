<?php

declare(strict_types=1);

enum SummerBannerPosition: string
{
    use BannerPositionTrait;

    private const WIDTH_CONFIG = [
        'left' => ['mobile' => 400, 'tablet' => 600, 'desktop' => 800],
        'center_left' => ['mobile' => 200, 'tablet' => 300, 'desktop' => 400],
        'center_right' => ['mobile' => 200, 'tablet' => 300, 'desktop' => 400],
        'right' => ['mobile' => 300, 'tablet' => 450, 'desktop' => 600],
    ];

    public function getRenderingConfig(): array
    {
        return match($this) {
            self::TOP_LEFT => [
            ],
            self::MIDDLE_LEFT => [
            ],
            self::BOTTOM_LEFT => [
            ],
            self::TOP_RIGHT => [
            ],
        };
    }

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    protected static function getWidthConfig(): array
    {
        return self::WIDTH_CONFIG;
    }

    case TOP_LEFT = 'top_left';
    case TOP_RIGHT = 'top_right';
    case MIDDLE_LEFT = 'middle_left';
    case BOTTOM_LEFT = 'bottom_left';
    case BOTTOM_RIGHT = 'bottom_right';
}