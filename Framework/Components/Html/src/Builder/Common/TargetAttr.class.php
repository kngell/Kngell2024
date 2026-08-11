<?php

declare(strict_types=1);

enum TargetAttr: string
{
    private function getLabel(): string
    {
        return ucwords(strtolower(str_replace('_', ' ', $this->name)));
    }

    public static function getOptions(): array
    {
        $options = ['' => ''];

        if (method_exists(static::class, 'cases')) {
            foreach (static::cases() as $case) {
                $options[$case->value] = $case->getLabel();
            }
        } else {
            // Fallback: use declared values and generate labels.
            foreach (static::getAllValues() as $val) {
                $options[$val] = ucwords(strtolower(str_replace('_', ' ', $val)));
            }
        }

        return $options;
    }

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'value');
    }
    case BLANK = '_blank';
    case SELF = '_self';
    case PARENT = '_parent';
    case TOP = '_top';
}