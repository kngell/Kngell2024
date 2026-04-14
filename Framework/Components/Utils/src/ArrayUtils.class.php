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

    public static function isDeepEmpty($value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if (!self::isDeepEmpty($item)) {
                    return false;
                }
            }
            return true;
        }
        return empty($value);
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

    // Add this to your ArrayUtils class
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

    public static function flattenArrayRecursive(?array $array = null): array
    {
        $flatArray = [];
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value) {
            $flatArray[] = $value;
        }
        return $flatArray;
    }

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

    public static function containsOnlyInstancesOf(array $array, string $className): bool
    {
        foreach ($array as $item) {
            if (!$item instanceof $className) {
                return false;
            }
        }
        return true;
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
        if (!self::isMultidimentional($array)) {
            return $array;
        }

        return self::flattenToSequential($array);
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

    public static function isKeyValueList(array $data): bool
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

    public static function fromSequentialToAssoc(array $data): array
    {
        $result = [];
        for ($i = 0; $i < count($data); $i += 2) {
            $result[$data[$i]] = $data[$i + 1];
        }

        return $result;
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
        if (!array_is_list($array)) {
            return false;
        }

        foreach ($array as $value) {
            if (!is_array($value) || !self::isAssoc($value)) {
                return false;
            }
        }

        return !empty($array);
    }

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
            'csrfToken', 'frm_name', 'form_name',
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

    private static function flattenAssociativeArray(array $assocArray): array
    {
        $result = [];
        foreach ($assocArray as $key => $value) {
            if (is_string($key)) {
                // Add both key and value as separate elements
                $result[] = $key;
                $result[] = $value;
                if (is_array($value) && self::isSequential($value)) {
                    return $result;
                }
            } else {
                // Numeric key - recursive flatten
                $result = array_merge($result, self::flattenToSequential([$value]));
            }
        }

        return $result;
    }

    private static function flattenToSequential(array $array): array
    {
        $result = [];

        foreach ($array as $value) {
            if (!is_array($value)) {
                $result[] = $value;
                continue;
            }

            if (self::isAssoc($value)) {
                // Associative array - flatten key-value pairs
                $result = array_merge($result, self::flattenAssociativeArray($value));
            } elseif (self::isSequential($value) && count($value) === 2) {
                if (is_string($value[0]) && is_array($value[1])) {
                    if (self::isSequential($value[1]) || self::isStringList($value[1])) {
                        return $value;
                    }
                }
                $result = $value;
            } else {
                // Sequential array - recursive flatten
                $result = array_merge($result, self::flattenToSequential($value));
            }
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