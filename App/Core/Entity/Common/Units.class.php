<?php

declare(strict_types=1);

enum Units: string
{
    // You can add methods too
    public function getLabel(): string
    {
        return match($this) {
            self::CM => 'Centimeters',
            self::M => 'Meters',
            self::INCH => 'Inches',
            self::FT => 'Feet',
        };
    }

    public function isMetric(): bool
    {
        return in_array($this, [self::CM, self::M]);
    }
    case CM = 'cm';
    case M = 'm';
    case INCH = 'inch';
    case FT = 'ft';
    case MM = 'mm';
}