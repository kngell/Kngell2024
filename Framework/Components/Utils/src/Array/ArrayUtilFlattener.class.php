<?php

declare(strict_types=1);

final class ArrayUtilFlattener
{
    /**
     * Flatten a multidimensional array recursively.
     */
    public static function flatten(array $array): array
    {
        $result = [];
        foreach ($array as $item) {
            if (is_array($item)) {
                $result = array_merge($result, self::flatten($item));
            } else {
                $result[] = $item;
            }
        }
        return $result;
    }

    /**
     * Flatten with SPL RecursiveIterator.
     */
    public static function flattenRecursive(?array $array = null): array
    {
        $flatArray = [];
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value) {
            $flatArray[] = $value;
        }
        return $flatArray;
    }

    /**
     * Flatten with keys using bracket notation.
     */
    public static function flattenWithKeys(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $currentKey = $prefix ? "{$prefix}[{$key}]" : $key;

            if (is_array($value) && !empty($value)) {
                if (ArrayTypeChecker::isSequential($value)) {
                    $result = array_merge($result, self::flattenWithKeys($value, $currentKey));
                } else {
                    $result = array_merge($result, self::flattenWithKeys($value, $currentKey));
                }
            } else {
                $result[$currentKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Expand flattened array back to nested structure.
     */
    public static function expandFromKeys(array $flatArray): array
    {
        $result = [];

        foreach ($flatArray as $key => $value) {
            $parts = preg_split('/[\[\]]+/', $key, -1, PREG_SPLIT_NO_EMPTY);
            self::setNestedValue($result, $parts, $value);
        }

        return $result;
    }

    private static function setNestedValue(array &$array, array $parts, mixed $value): void
    {
        $currentPart = array_shift($parts);

        if (empty($parts)) {
            $array[$currentPart] = $value;
        } else {
            if (!isset($array[$currentPart]) || !is_array($array[$currentPart])) {
                $array[$currentPart] = [];
            }
            self::setNestedValue($array[$currentPart], $parts, $value);
        }
    }
}