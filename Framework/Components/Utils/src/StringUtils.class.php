<?php

declare(strict_types=1);

final readonly class StringUtils
{
    private function __construct()
    {
        // Prevent instantiation
    }

    public static function addTrailing(string $string, string $trailing): string
    {
        if (str_ends_with($string, $trailing)) {
            return $string;
        }
        return $string . $trailing;
    }

    public static function addLeading(string $string, string $leading): string
    {
        if (str_starts_with($string, $leading)) {
            return $string;
        }
        return $leading . $string;
    }

    public static function isBlank(?string $string): bool
    {
        return $string === null || trim($string) === '';
    }

    public static function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (!is_string($needle)) {
                continue;
            }
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }
        return false;
    }

    public static function camelCase(string $string): string
    {
        return lcfirst(self::studlyCaps($string));
    }

    public static function studlyCaps(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));
    }

    public static function studlyCapsToUnderscore(string $value): string
    {
        return strtolower(self::capsToSeparator($value, '_'));
    }

    public static function camelCaseToSnakeCase(string $input): string
    {
        return strtolower(preg_replace('/[A-Z]/', '_$0', lcfirst($input)));
    }

    public static function camelCaseToKebabCase(string $input): string
    {
        return strtolower(preg_replace('/[A-Z]/', '-$0', lcfirst($input)));
    }

    public static function kebabCase(string $string): string
    {
        // Convert StudlyCaps/CamelCase to kebab-case
        $kebab = self::camelCaseToKebabCase($string);

        // Handle strings with spaces/underscores
        $kebab = str_replace([' ', '_'], '-', $kebab);

        // Remove duplicate hyphens and trim
        $kebab = preg_replace('/-+/', '-', $kebab);
        $kebab = trim($kebab, '-');

        return strtolower($kebab);
    }

    public static function isSerialized($data, bool $strict = true): bool
    {
        // If it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }

        $data = trim($data);

        if ($data === 'N;') {
            return true;
        }

        if (strlen($data) < 4) {
            return false;
        }

        if ($data[1] !== ':') {
            return false;
        }

        if ($strict) {
            $lastChar = substr($data, -1);
            if ($lastChar !== ';' && $lastChar !== '}') {
                return false;
            }
        } else {
            $semicolonPos = strpos($data, ';');
            $bracePos = strpos($data, '}');

            // Either ; or } must exist.
            if ($semicolonPos === false && $bracePos === false) {
                return false;
            }

            // But neither must be in the first X characters.
            if ($semicolonPos !== false && $semicolonPos < 3) {
                return false;
            }
            if ($bracePos !== false && $bracePos < 4) {
                return false;
            }
        }

        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if (substr($data, -2, 1) !== '"') {
                        return false;
                    }
                } elseif (!str_contains($data, '"')) {
                    return false;
                }
                // Fall through intentionally

                // no break
            case 'a':
            case 'O':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);

            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';
                return (bool) preg_match("/^{$token}:[0-9.E+-]+;{$end}/", $data);

            default:
                return false;
        }
    }

    private static function capsToSeparator(string $value, string $separator): string
    {
        if (defined('PREG_BAD_UTF8_OFFSET_ERROR') && preg_match('/\pL/u', 'a') === 1) {
            $pattern = ['#(?<=(?:\p{Lu}))(\p{Lu}\p{Ll})#', '#(?<=(?:\p{Ll}|\p{Nd}))(\p{Lu})#'];
            $replacement = [$separator . '\1', $separator . '\1'];
        } else {
            $pattern = ['#(?<=(?:[A-Z]))([A-Z]+)([A-Z][a-z])#', '#(?<=(?:[a-z0-9]))([A-Z])#'];
            $replacement = ['\1' . $separator . '\2', $separator . '\1'];
        }

        return preg_replace($pattern, $replacement, $value) ?? $value;
    }
}