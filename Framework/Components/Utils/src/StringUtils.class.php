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

    public static function camelCaseToSnakeCase(string $input): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($input)));
    }

    public static function is_serialized($data, $strict = true) : bool
    {
        // If it isn't a string, it isn't serialized.
        if (! is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' === $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
                // Or else fall through.
                // no break
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
        }
        return false;
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
