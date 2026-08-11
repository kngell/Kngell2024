<?php

declare(strict_types=1);

final class ArraySearch
{
    public static function hasValue(array $array, string $search, bool $caseSensitive = false): bool
    {
        if ($caseSensitive) {
            return in_array($search, $array, true);
        }

        $searchLower = strtolower($search);
        foreach ($array as $value) {
            if (is_string($value) && strtolower($value) === $searchLower) {
                return true;
            }
        }
        return false;
    }

    public static function hasAnyValue(array $array, array $searchValues, bool $caseSensitive = false): bool
    {
        foreach ($searchValues as $search) {
            if (self::hasValue($array, $search, $caseSensitive)) {
                return true;
            }
        }
        return false;
    }

    public static function findIndex(array $array, callable $searchFn): string|int|null
    {
        foreach ($array as $key => $value) {
            if ($searchFn($value)) {
                return $key;
            }
        }
        return null;
    }
}