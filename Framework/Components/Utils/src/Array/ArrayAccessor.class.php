<?php

declare(strict_types=1);

final class ArrayAccessor
{
    /**
     * Deeply search for a value in a nested array structure.
     * Supports dot notation, array key paths, and wildcards.
     */
    public static function deepGet(array $array, string|array $keys, mixed $default = null, string $separator = '.'): mixed
    {
        $keys = is_string($keys) ? explode($separator, $keys) : $keys;
        $current = $array;

        foreach ($keys as $key) {
            if ($key === '*') {
                foreach ($current as $item) {
                    if (is_array($item)) {
                        $remainingKeys = array_slice($keys, array_search($key, $keys) + 1);
                        if (empty($remainingKeys)) {
                            return $item;
                        }
                        $result = self::deepGet($item, $remainingKeys, $default, $separator);
                        if ($result !== $default) {
                            return $result;
                        }
                    }
                }
                return $default;
            }

            if (!is_array($current) || !array_key_exists($key, $current)) {
                return $default;
            }

            $current = $current[$key];
        }

        return $current;
    }

    /**
     * Get all values matching a path pattern with wildcards.
     */
    public static function deepGetAll(array $array, string|array $keys, string $separator = '.'): array
    {
        $keys = is_string($keys) ? explode($separator, $keys) : $keys;
        $results = [];
        self::deepGetAllRecursive($array, $keys, $results);
        return $results;
    }

    /**
     * Check if a deeply nested key exists.
     */
    public static function deepHas(array $array, string|array $keys, string $separator = '.'): bool
    {
        return self::deepGet($array, $keys, null, $separator) !== null;
    }

    /**
     * Get value using dot notation (alias).
     */
    public static function dot(array $array, string $key, mixed $default = null): mixed
    {
        return self::deepGet($array, $key, $default);
    }

    /**
     * Try multiple keys to find a value.
     */
    public static function findValue(array $array, string|array $searchKeys, mixed $default = null): mixed
    {
        $searchKeys = is_string($searchKeys) ? [$searchKeys] : $searchKeys;

        foreach ($searchKeys as $key) {
            $result = self::deepGet($array, $key, null);
            if ($result !== null) {
                return $result;
            }

            if (array_key_exists($key, $array)) {
                return $array[$key];
            }
        }

        return $default;
    }

    /**
     * Get first element of array.
     */
    public static function first(array $array): mixed
    {
        return empty($array) ? null : array_values($array)[0];
    }

    /**
     * Get all values from array (reset keys).
     */
    public static function values(array $array): array
    {
        return array_values($array);
    }

    private static function deepGetAllRecursive(array $array, array $keys, array &$results): void
    {
        if (empty($keys)) {
            $results[] = $array;
            return;
        }

        $currentKey = array_shift($keys);

        if ($currentKey === '*') {
            foreach ($array as $item) {
                if (is_array($item)) {
                    if (empty($keys)) {
                        $results[] = $item;
                    } else {
                        self::deepGetAllRecursive($item, $keys, $results);
                    }
                }
            }
        } else {
            if (is_array($array) && array_key_exists($currentKey, $array)) {
                if (empty($keys)) {
                    $results[] = $array[$currentKey];
                } else {
                    self::deepGetAllRecursive($array[$currentKey], $keys, $results);
                }
            }
        }
    }
}