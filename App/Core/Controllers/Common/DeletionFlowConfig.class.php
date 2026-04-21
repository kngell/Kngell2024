<?php

declare(strict_types=1);

enum DeletionFlowConfig: string
{
    public static function flashKeyFor(string $label): string
    {
        return self::FLASH_KEY_PREFIX->value
            . strtolower(str_replace(' ', '_', $label));
    }
    case FLASH_KEY_PREFIX = 'delete_data_';
    case DEFAULT_REDIRECT = '/admin/dashboard';
}