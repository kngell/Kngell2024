<?php

declare(strict_types=1);

/**
 * ArrayUtils - Main facade for array operations.
 * Provides a unified interface to all array utility classes.
 * Maintains backward compatibility with all original methods.
 */
final class ArrayUtils
{
    private function __construct()
    {
    }

    // ─── Access ───────────────────────────────────────────────
    public static function deepGet(array $array, string|array $keys, mixed $default = null, string $separator = '.'): mixed
    {
        return ArrayAccessor::deepGet($array, $keys, $default, $separator);
    }

    public static function deepGetAll(array $array, string|array $keys, string $separator = '.'): array
    {
        return ArrayAccessor::deepGetAll($array, $keys, $separator);
    }

    public static function deepHas(array $array, string|array $keys, string $separator = '.'): bool
    {
        return ArrayAccessor::deepHas($array, $keys, $separator);
    }

    public static function dot(array $array, string $key, mixed $default = null): mixed
    {
        return ArrayAccessor::dot($array, $key, $default);
    }

    public static function findValue(array $array, string|array $searchKeys, mixed $default = null): mixed
    {
        return ArrayAccessor::findValue($array, $searchKeys, $default);
    }

    public static function first(array $array): mixed
    {
        return ArrayAccessor::first($array);
    }

    public static function valuesFromArray(array $array): array
    {
        return ArrayAccessor::values($array);
    }

    // ─── Type Checking ────────────────────────────────────────
    public static function isAssoc(array $array): bool
    {
        return ArrayTypeChecker::isAssoc($array);
    }

    public static function isSequential(array $array): bool
    {
        return ArrayTypeChecker::isSequential($array);
    }

    public static function isMultidimentional(array $array): bool
    {
        return ArrayTypeChecker::isMultidimentional($array);
    }

    public static function isMixed(array $array): bool
    {
        return ArrayTypeChecker::isMixed($array);
    }

    public static function isStringList(array $array): bool
    {
        return ArrayTypeChecker::isStringList($array);
    }

    public static function isObjectList(array $array): bool
    {
        return ArrayTypeChecker::isObjectList($array);
    }

    public static function isArrayList(array $array): bool
    {
        return ArrayTypeChecker::isArrayList($array);
    }

    public static function isSequentialKeyValueList(array $data): bool
    {
        return ArrayTypeChecker::isSequentialKeyValueList($data);
    }

    public static function isLikeKeyValuePair(array $data): bool
    {
        return ArrayTypeChecker::isLikeKeyValuePair($data);
    }

    public static function hasMixedTypes(array $array): bool
    {
        return ArrayTypeChecker::hasMixedTypes($array);
    }

    public static function containsOnlyInstancesOf(array $array, string $className): bool
    {
        return ArrayTypeChecker::containsOnlyInstancesOf($array, $className);
    }

    public static function hasNestedStructure(array $data): bool
    {
        return ArrayTypeChecker::hasNestedStructure($data);
    }

    // ─── Flattening ───────────────────────────────────────────
    public static function flatten(array $array): array
    {
        return ArrayUtilFlattener::flatten($array);
    }

    public static function flattenArrayRecursive(?array $array = null): array
    {
        return ArrayUtilFlattener::flattenRecursive($array);
    }

    public static function flattenWithKeys(array $array, string $prefix = ''): array
    {
        return ArrayUtilFlattener::flattenWithKeys($array, $prefix);
    }

    public static function expandFromKeys(array $flatArray): array
    {
        return ArrayUtilFlattener::expandFromKeys($flatArray);
    }

    // ─── Formatting ───────────────────────────────────────────
    public static function fromAssocToSequential(array $array): array
    {
        return ArrayFormatter::fromAssocToSequential($array);
    }

    public static function fromSequentialToAssoc(array $data): array
    {
        return ArrayFormatter::fromSequentialToAssoc($data);
    }

    public static function extractFormData(array $formData): array
    {
        return ArrayFormatter::extractFormData($formData);
    }

    public static function prepareFormValues(array $data, bool $forceFlatten = false): array
    {
        return ArrayFormatter::prepareFormValues($data, $forceFlatten);
    }

    public static function ensureFormStructure(array $data): array
    {
        return ArrayFormatter::ensureFormStructure($data);
    }

    // ─── Search ───────────────────────────────────────────────
    public static function hasValue(array $array, string $search, bool $caseSensitive = false): bool
    {
        return ArraySearch::hasValue($array, $search, $caseSensitive);
    }

    public static function hasAnyValue(array $array, array $searchValues, bool $caseSensitive = false): bool
    {
        return ArraySearch::hasAnyValue($array, $searchValues, $caseSensitive);
    }

    public static function findIndex(array $array, callable $searchFn): string|int|null
    {
        return ArraySearch::findIndex($array, $searchFn);
    }

    // ─── Merging ──────────────────────────────────────────────
    public static function preserveArrayMerge(array $array1, array $array2): array
    {
        return ArrayMerger::preserveArrayMerge($array1, $array2);
    }

    public static function mergeFormData(array $existing, array $new): array
    {
        return ArrayMerger::mergeFormData($existing, $new);
    }

    public static function doArraysHasTheSameValues(array $array1, array $array2, bool $ignoreCurrentSort = true): bool
    {
        return ArrayMerger::hasSameValues($array1, $array2, $ignoreCurrentSort);
    }

    // ─── Filtering ────────────────────────────────────────────
    public static function filterSystemFields(array $data): array
    {
        return ArrayFilter::filterSystemFields($data);
    }

    public static function getFormFields(array $data): array
    {
        return ArrayFilter::getFormFields($data);
    }

    public static function cleanFormData(array $data): array
    {
        return ArrayFilter::cleanFormData($data);
    }

    public static function isDeepEmpty(mixed $value): bool
    {
        return ArrayFilter::isDeepEmpty($value);
    }
}
