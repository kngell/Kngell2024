<?php

declare(strict_types=1);

enum SectionMapping: string
{
    public function getConfigClass(): string
    {
        return match($this) {
            self::MEDIA => MediaSectionConfig::class,
            self::REGULAR => RegularSectionConfig::class,
        };
    }

    public function getConfigMethod(): string
    {
        return match($this) {
            self::MEDIA => 'setConfig',
            self::REGULAR => 'setConfig',
        };
    }
    case MEDIA = FormMediaSection::class;
    case REGULAR = FormRegularSection::class;
}