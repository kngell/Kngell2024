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

    public static function strContainsArrayItem(string $str, array $array) : bool
    {
        foreach ($array as $item) {
            if (strpos($str, $item) !== false) {
                return true;
            }
        }
        return false;
    }
    // public static function studlyCaps(string $str) : string
    // {
    //     return str_replace('-', '', ucwords(strtolower($str), '-'));
    // }

    // public static function camelCase(string $str) : string
    // {
    //     return lcfirst(str_replace('-', '', ucwords(strtolower($str), '-')));
    // }
    public static function camelCase(string $string) : string
    {
        return lcfirst(self::studlyCaps($string));
    }

    public static function studlyCaps(string $string) : string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
    }

    public static function StudlyCapsToUnderscore($value)
    {
        return strtolower(self::CapsToUnderscore($value, '_'));
    }

    private static function CapsToUnderscore($value, $separator = ' ')
    {
        if (! is_scalar($value) && ! is_array($value)) {
            return $value;
        }
        if (defined('PREG_BAD_UTF8_OFFSET_ERROR') && preg_match('/\pL/u', 'a') == 1) {
            $pattern = ['#(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})#', '#(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})#'];
            $replacement = [$separator . '\1', $separator . '\1'];
        } else {
            $pattern = ['#(?<=(?:[A-Z]))([A-Z]+)([A-Z][a-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#'];
            $replacement = ['\1' . $separator . '\2', $separator . '\1'];
        }
        return preg_replace($pattern, $replacement, $value);
    }
}