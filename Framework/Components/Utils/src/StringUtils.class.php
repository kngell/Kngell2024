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

    public static function studlyCaps(string $str) : string
    {
        return str_replace('-', '', ucwords(strtolower($str), '-'));
    }

    public static function camelCase(string $str) : string
    {
        return lcfirst(str_replace('-', '', ucwords(strtolower($str), '-')));
    }
}