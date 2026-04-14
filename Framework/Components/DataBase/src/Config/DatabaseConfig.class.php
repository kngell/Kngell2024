<?php

declare(strict_types=1);
class DatabaseConfig
{
    private static bool $useMariadbDialect = true;

    public static function useMariadbDialect(bool $use): void
    {
        self::$useMariadbDialect = $use;
    }

    public static function isMariadbDialect(): bool
    {
        return self::$useMariadbDialect;
    }
}