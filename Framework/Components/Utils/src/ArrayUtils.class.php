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
}