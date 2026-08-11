<?php

declare(strict_types=1);

final class ArrayTypeChecker
{
    public static function isAssoc(array $array): bool
    {
        if ([] === $array) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    public static function isSequential(array $array): bool
    {
        if (empty($array)) {
            return true;
        }
        return array_keys($array) === range(0, count($array) - 1);
    }

    public static function isMultidimentional(array $array): bool
    {
        return count($array) !== count($array, COUNT_RECURSIVE);
    }

    public static function isMixed(array $array): bool
    {
        $keys = array_keys($array);
        $count = count($keys);
        if ($count === 0) {
            return false;
        }

        $stringKeys = 0;
        foreach ($keys as $key) {
            if (is_string($key)) {
                $stringKeys++;
            }
        }
        return $stringKeys > 0 && $stringKeys < $count;
    }

    public static function isStringList(array $array): bool
    {
        if (!array_is_list($array)) {
            return false;
        }

        foreach ($array as $value) {
            if (!is_string($value)) {
                return false;
            }
        }

        return true;
    }

    public static function isObjectList(array $array): bool
    {
        if (!array_is_list($array)) {
            return false;
        }

        foreach ($array as $value) {
            if (!is_object($value)) {
                return false;
            }
        }

        return true;
    }

    public static function isArrayList(array $array): bool
    {
        if (!array_is_list($array) || empty($array)) {
            return false;
        }

        foreach ($array as $value) {
            if (!is_array($value) || !self::isAssoc($value)) {
                return false;
            }
        }

        return true;
    }

    public static function isSequentialKeyValueList(array $data): bool
    {
        $count = count($data);
        if ($count % 2 !== 0 || $count < 2) {
            return false;
        }
        for ($i = 0; $i < $count; $i += 2) {
            if (!is_string($data[$i])) {
                return false;
            }
        }

        return true;
    }

    public static function isLikeKeyValuePair(array $data): bool
    {
        $count = count($data);

        // Must have even number of elements (key-value pairs)
        if ($count % 2 !== 0 || $count === 0) {
            return false;
        }

        // Validate all keys (even indexes) are strings
        for ($i = 0; $i < $count; $i += 2) {
            if (!is_string($data[$i])) {
                return false;
            }
        }

        return true;
    }

    public static function hasMixedTypes(array $array): bool
    {
        if (empty($array)) {
            return false;
        }

        $uniqueTypes = array_unique(array_map('gettype', $array));
        return count($uniqueTypes) > 1;
    }

    public static function containsOnlyInstancesOf(array $array, string $className): bool
    {
        foreach ($array as $item) {
            if (!$item instanceof $className) {
                return false;
            }
        }
        return true;
    }

    public static function hasNestedStructure(array $data): bool
    {
        foreach ($data as $key => $value) {
            if (is_string($key) && (str_contains($key, '[') || str_contains($key, ']'))) {
                return false;
            }
            if (is_array($value) && self::isAssoc($value)) {
                return true;
            }
        }
        return false;
    }
}