<?php

declare(strict_types=1);

final readonly class ArrayUtils
{
    private function __construct()
    {
    }

    public static function first(array $array) : mixed
    {
        if (empty($array)) {
            return null;
        }
        return array_values($array)[0];
    }

    public static function doArraysHasTheSameValues(array $array1, array $array2, bool $ignoreCurrentSort = true) : bool
    {
        if ($ignoreCurrentSort) {
            sort($array1);
            sort($array2);
        }
        return $array1 == $array2;
    }

    public static function findIndex(array $array, callable $searchFn) : string|int|null
    {
        foreach ($array as $key => $value) {
            if ($searchFn($value)) {
                return $key;
            }
        }
        return null;
    }

    public static function isAssoc(array $array) : bool
    {
        $keys = array_keys($array);
        return array_keys($keys) !== $keys;
    }

    public static function isMultidimentional(array $array) : bool
    {
        if (count($array) == count($array, COUNT_RECURSIVE)) {
            return false;
        }
        return true;
    }

    public static function flattenArrayRecursive(?array $array = null): array
    {
        $flatArray = [];
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value) {
            $flatArray[] = $value;
        }
        return $flatArray;
    }

    public static function flatten_with_keys(array $array): array
    {
        $recursiveArrayIterator = new RecursiveArrayIterator(
            $array,
            RecursiveArrayIterator::CHILD_ARRAYS_ONLY
        );
        $iterator = new RecursiveIteratorIterator($recursiveArrayIterator);
        return iterator_to_array($iterator);
    }

    public static function FromAssocToSequential(array $array) : array
    {
        $newArr = [];
        if (self::isMultidimentional($array)) {
            foreach ($array as $key => $value) {
                if (! is_array($value)) {
                    $newArr[] = $value;
                    unset($array[$key]);
                } elseif (self::isAssoc($value)) {
                    foreach ($value as $vKey => $vValue) {
                        $newArr[] = $vKey;
                        $newArr[] = $vValue;
                    }
                    unset($array[$key]);
                } elseif (self::isSequential($value)) {
                    $newArr = array_merge($newArr, self::FromAssocToSequential($value));
                    unset($array[$key]);
                }
            }
            if (empty($array)) {
                return $newArr;
            }
        }
        return $array;
    }

    public static function isSequential(array &$arr, int $base = 0) : bool
    {
        for (reset($arr), $base = (int) $base; key($arr) === $base++; next($arr));
        return is_null(key($arr));
    }

    public static function valuesFromArray($array) : array
    {
        $values = [];
        foreach ($array as $key => $value) {
            $values[] = $value;
        }
        return $values;
    }
}