<?php

declare(strict_types=1);

enum WeightUnits: string
{
    public function getSymbol(): string
    {
        return match($this) {
            self::GRAM => 'g',
            self::KILOGRAM => 'kg',
            self::POUND => 'lb',
            self::OUNCE => 'oz',
        };
    }

    public function getLabel(): string
    {
        return match($this) {
            self::GRAM => 'Grams',
            self::KILOGRAM => 'Kilograms',
            self::POUND => 'Pounds',
            self::OUNCE => 'Ounces',
        };
    }

    public function isMetric(): bool
    {
        return in_array($this, [self::GRAM, self::KILOGRAM]);
    }

    case KILOGRAM = 'kg';
    case GRAM = 'g';
    case POUND = 'lb';
    case OUNCE = 'oz';
}