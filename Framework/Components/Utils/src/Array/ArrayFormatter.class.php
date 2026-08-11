<?php

declare(strict_types=1);

final class ArrayFormatter
{
    public static function fromAssocToSequential(array $array): array
    {
        if (!ArrayTypeChecker::isMultidimentional($array)) {
            return $array;
        }

        return self::flattenToSequential($array);
    }

    /**
     * Convert sequential key-value list to associative array.
     */
    public static function fromSequentialToAssoc(array $data): array
    {
        $result = [];
        for ($i = 0; $i < count($data); $i += 2) {
            $result[$data[$i]] = $data[$i + 1];
        }

        return $result;
    }

    /**
     * Extract form data (handles both flattened and nested).
     */
    public static function extractFormData(array $formData): array
    {
        if (ArrayTypeChecker::hasNestedStructure($formData)) {
            return $formData;
        }

        return ArrayUtilFlattener::expandFromKeys($formData);
    }

    /**
     * Prepare form values (flatten if needed).
     */
    public static function prepareFormValues(array $data, bool $forceFlatten = false): array
    {
        if (!$forceFlatten && ArrayTypeChecker::hasNestedStructure($data) === false) {
            return $data;
        }

        return ArrayUtilFlattener::flattenWithKeys($data);
    }

    /**
     * Ensure form data has proper structure (empty arrays become empty strings).
     */
    public static function ensureFormStructure(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
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

    private static function flattenAssociativeArray(array $assocArray): array
    {
        $result = [];
        foreach ($assocArray as $key => $value) {
            if (is_string($key)) {
                $result[] = $key;
                $result[] = $value;
                if (is_array($value) && ArrayTypeChecker::isSequential($value)) {
                    return $result;
                }
            } else {
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

            if (ArrayTypeChecker::isAssoc($value)) {
                $result = array_merge($result, self::flattenAssociativeArray($value));
            } elseif (ArrayTypeChecker::isSequential($value) && count($value) === 2) {
                if (is_string($value[0]) && is_array($value[1])) {
                    if (ArrayTypeChecker::isSequential($value[1]) || ArrayTypeChecker::isStringList($value[1])) {
                        return $value;
                    }
                }
                $result = $value;
            } else {
                $result = array_merge($result, self::flattenToSequential($value));
            }
        }

        return $result;
    }
}