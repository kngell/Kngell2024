<?php

declare(strict_types=1);

final readonly class StringUtils
{
    private function __construct()
    {
    }

    public static function addTrailingString(string $str, string $trailingStr) : string
    {
        if (str_ends_with($str, $trailingStr)) {
            return $str;
        }
        return $str . $trailingStr;
    }

    public static function addBeginningString(string $str, string $trailingStr) : string
    {
        if (str_starts_with($str, $trailingStr)) {
            return $str;
        }
        return $trailingStr . $str;
    }

    public static function isBlanc(string|null $str) : bool
    {
        return $str === null || empty(trim($str));
    }
}
