<?php

declare(strict_types=1);

final readonly class ArrayUtils
{
    private function __construct()
    {
    }

    public static function first(array $array): mixed
    {
        if (empty($array)) {
            return null;
        }
        return array_values($array)[0];
    }

    public static function doArraysHasTheSameValues(array $array1, array $array2, bool $ignoreCurrentSort = true): bool
    {
        if ($ignoreCurrentSort) {
            sort($array1);
            sort($array2);
        }
        return $array1 == $array2;
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

    public static function isAssoc(array $array): bool
    {
        if ([] === $array) {
            return false;
        }
        return array_keys($array) !== range(0, count($array) - 1);
    }

    public static function isMultidimentional(array $array): bool
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

    /**
     * Flatten array with proper form field names
     * Handles nested arrays and ensures proper bracket notation.
     */
    public static function flattenWithKeys(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $currentKey = $prefix ? "{$prefix}[{$key}]" : $key;

            if (is_array($value) && !empty($value)) {
                // Handle both associative and sequential arrays
                if (self::isSequential($value)) {
                    // Sequential array - use index-based keys
                    $result = array_merge($result, self::flattenWithKeys($value, $currentKey));
                } else {
                    // Associative array - merge the results
                    $result = array_merge($result, self::flattenWithKeys($value, $currentKey));
                }
            } else {
                // Scalar value or empty array
                $result[$currentKey] = $value;
            }
        }

        return $result;
    }

    public static function expandFromKeys(array $flatArray): array
    {
        $result = [];

        foreach ($flatArray as $key => $value) {
            $parts = preg_split('/[\[\]]+/', $key, -1, PREG_SPLIT_NO_EMPTY);
            self::setNestedValue($result, $parts, $value);
        }

        return $result;
    }

    public static function fromAssocToSequential(array $array): array
    {
        $newArr = [];
        if (self::isMultidimentional($array)) {
            foreach ($array as $key => $value) {
                if (!is_array($value)) {
                    $newArr[] = $value;
                    unset($array[$key]);
                } elseif (self::isAssoc($value)) {
                    foreach ($value as $vKey => $vValue) {
                        $newArr[] = $vKey;
                        $newArr[] = $vValue;
                    }
                    unset($array[$key]);
                } elseif (self::isSequential($value)) {
                    $newArr = array_merge($newArr, self::fromAssocToSequential($value));
                    unset($array[$key]);
                }
            }
            if (empty($array)) {
                return $newArr;
            }
        }
        return $array;
    }

    /**
     * Check if array is sequential (numeric keys starting from 0).
     */
    public static function isSequential(array $arr): bool
    {
        if (empty($arr)) {
            return true;
        }
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    public static function valuesFromArray(array $array): array
    {
        return array_values($array);
    }

    /**
     * Extract form data with nested structure from flattened form input
     * Handles both raw form data and already expanded data.
     */
    public static function extractFormData(array $formData): array
    {
        // If data is already nested (like from model), return as-is
        if (self::hasNestedStructure($formData)) {
            return $formData;
        }

        // Otherwise, expand from flattened keys
        return self::expandFromKeys($formData);
    }

    /**
     * Check if array already has nested structure (not flattened).
     */
    public static function hasNestedStructure(array $data): bool
    {
        foreach ($data as $key => $value) {
            // If any key contains brackets, it's flattened form data
            if (is_string($key) && (str_contains($key, '[') || str_contains($key, ']'))) {
                return false;
            }
            // If any value is an array with string keys, it's nested
            if (is_array($value) && self::isAssoc($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Prepare form values ensuring they're in the correct format for form builder.
     */
    public static function prepareFormValues(array $data, bool $forceFlatten = false): array
    {
        // If data is already flattened or we need to force flatten, return as-is
        if (!$forceFlatten && self::hasNestedStructure($data) === false) {
            return $data;
        }

        // Otherwise flatten the nested structure
        return self::flattenWithKeys($data);
    }

    /**
     * Clean form data by removing empty arrays that might cause issues.
     */
    public static function cleanFormData(array $data): array
    {
        foreach ($data as $key => &$value) {
            if (is_array($value)) {
                $value = self::cleanFormData($value);
                // Remove empty arrays
                if (empty($value)) {
                    unset($data[$key]);
                }
            }
        }
        return $data;
    }

    /**
     * Get only the form field data (exclude system fields).
     */
    public static function getFormFields(array $data): array
    {
        $systemFields = ['csrfToken', 'frm_name', 'public_id', 'form_name', 'form_action'];
        return array_diff_key($data, array_flip($systemFields));
    }

    /**
     * Preserve array structure while merging (useful for variations).
     */
    public static function preserveArrayMerge(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                // For sequential arrays (like variations), replace entirely
                if (self::isSequential($value) && self::isSequential($merged[$key])) {
                    $merged[$key] = $value;
                } else {
                    // For associative arrays, merge recursively
                    $merged[$key] = self::preserveArrayMerge($merged[$key], $value);
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Filter out system fields from form data.
     */
    public static function filterSystemFields(array $data): array
    {
        $systemFields = [
            'csrfToken', 'frm_name', 'public_id', 'form_name',
            'form_action', '_token', '_method', 'submit',
        ];

        return array_diff_key($data, array_flip($systemFields));
    }

    public static function ensureFormStructure(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // For empty arrays, store as empty string for form compatibility
                if (empty($value)) {
                    $result[$key] = '';
                } else {
                    $result[$key] = self::ensureFormStructure($value);
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Merge form data with existing data, handling nested structures.
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