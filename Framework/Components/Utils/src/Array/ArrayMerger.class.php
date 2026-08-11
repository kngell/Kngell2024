<?php

declare(strict_types=1);

final class ArrayMerger
{
    /**
     * Merge arrays while preserving structure.
     */
    public static function preserveArrayMerge(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                if (ArrayTypeChecker::isSequential($value) && ArrayTypeChecker::isSequential($merged[$key])) {
                    $merged[$key] = $value;
                } else {
                    $merged[$key] = self::preserveArrayMerge($merged[$key], $value);
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Merge form data with existing data.
     */
    public static function mergeFormData(array $existing, array $new): array
    {
        $merged = $existing;

        foreach ($new as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::mergeFormData($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Check if two arrays have the same values.
     */
    public static function hasSameValues(array $array1, array $array2, bool $ignoreCurrentSort = true): bool
    {
        if ($ignoreCurrentSort) {
            sort($array1);
            sort($array2);
        }
        return $array1 == $array2;
    }
}