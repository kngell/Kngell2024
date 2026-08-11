<?php

declare(strict_types=1);

enum SmallBannerTheme: string
{
    private function getLabel(): string
    {
        return ucwords(strtolower(str_replace('_', ' ', $this->name)));
    }

    public static function getOptions(): array
    {
        $options = [
            '' => '',
        ];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }
    case LIGHT = 'light';
    case DARK = 'dark';
    case WHITE = 'white';
    case GRAY_LIGHT = 'gray_light';
    case GRAY_NORMAL = 'gray_normal';
    case GRAY_DARK = 'gray_dark';
}