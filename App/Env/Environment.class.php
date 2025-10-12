<?php

declare(strict_types=1);

class Environment
{
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }

    public static function isProduction(): bool
    {
        return self::get('APP_ENV') === 'production';
    }

    public static function isDevelopment(): bool
    {
        return self::get('APP_ENV') === 'development';
    }

    public static function isTesting(): bool
    {
        return self::get('APP_ENV') === 'testing';
    }

    public static function isDebug(): bool
    {
        return (bool) self::get('DEBUG', false);
    }
}