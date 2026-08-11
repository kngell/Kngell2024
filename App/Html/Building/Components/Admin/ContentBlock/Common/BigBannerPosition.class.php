<?php

declare(strict_types=1);

enum BigBannerPosition: string
{
    use BannerPositionTrait; // For getWidth(), getWidths(), etc.

    private const WIDTH_CONFIG = [
        'left' => ['mobile' => 400, 'tablet' => 600, 'desktop' => 800],
        'center_left' => ['mobile' => 200, 'tablet' => 300, 'desktop' => 400],
        'center_right' => ['mobile' => 200, 'tablet' => 300, 'desktop' => 400],
        'right' => ['mobile' => 300, 'tablet' => 450, 'desktop' => 600],
    ];

    public function getRenderingConfig(): array
    {
        return match($this) {
            self::LEFT => [
                'card_class' => ['big-card', 'big-card__bg-white', 'big-card-multiple'],
                'image_container_class' => ['big-card__img-container', 'big-card-multiple__img-container'],
                'image_processor' => 'processMultipleImages',
                'width_override' => ['desktop' => 600],
            ],
            self::CENTER_LEFT => [
                'card_class' => ['big-card', 'big-card__bg-gray-light'],
                'image_container_class' => ['big-card__img-container--ipad'],
                'image_processor' => 'processSingleImages',
            ],
            self::CENTER_RIGHT => [
                'card_class' => ['big-card', 'big-card__bg-gray-normal'],
                'image_container_class' => ['big-card__img-container--samsung'],
                'image_processor' => 'processSingleImages',
            ],
            self::RIGHT => [
                'card_class' => ['big-card', 'big-card__bg-gray-dark'],
                'image_container_class' => ['big-card__img-container--macbook'],
                'image_processor' => 'processSingleImages',
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

    case LEFT = 'left';
    case CENTER_LEFT = 'center_left';
    case CENTER_RIGHT = 'center_right';
    case RIGHT = 'right';
}