<?php

declare(strict_types=1);
class RequestEntityPool
{
    private static array $pool = [];
    private static bool $enabled = true;

    public static function enable(): void
    {
        self::$enabled = true;
    }

    public static function disable(): void
    {
        self::$enabled = false;
        self::$pool = [];
    }

    public static function has(string $cacheKey): bool
    {
        return self::$enabled && isset(self::$pool[$cacheKey]);
    }

    public static function add(string $cacheKey): void
    {
        if (self::$enabled) {
            self::$pool[$cacheKey] = true;
        }
    }

    public static function clear(): void
    {
        self::$pool = [];
    }

    public static function getCount(): int
    {
        return count(self::$pool);
    }
}