<?php

declare(strict_types=1);
final readonly class EntityFieldMapper
{
    public function __construct(
        private TypePresenterFactory $typePresenterFactory,
    ) {
    }

    public function applyMapping(Entity $source, ?FormFieldMappingPayloadInterface $mapping = null, bool $formatValues = true): array
    {
        $mappedData = [];
        foreach ($mapping->getFieldMapping() as $sourcePath => $targetPath) {
            $isArrayTarget = str_ends_with($targetPath, '[]');
            if ($isArrayTarget) {
                $targetPath = rtrim($targetPath, '[]');
            }

            if (str_contains($sourcePath, '.*.')) {
                $this->handleWildcardMapping($source, $mappedData, $sourcePath, $targetPath, $formatValues, $mapping->getNumericFields(), $isArrayTarget);
            } else {
                $value = $this->getNestedValue($source, $sourcePath, $formatValues);
                $value = $this->applyFieldConstraints($targetPath, $value, $mapping?->getNumericFields() ?? []);

                if ($isArrayTarget && is_array($value)) {
                    // Handle array values
                    foreach ($value as $item) {
                        $this->setNestedValue($mappedData, $targetPath . '[]', $item);
                    }
                } else {
                    $this->setNestedValue($mappedData, $targetPath, $value);
                }
            }
        }
        return $mappedData;
    }

    private function handleWildcardMapping(Entity|array $data, array &$mappedData, string $source, string $target, bool $formatValues, array $numericKeys = [], bool $isArrayTarget = false): void
    {
        $sourceParts = explode('.*.', $source, 2);
        $targetParts = explode('.*.', $target, 2);

        $sourceBase = $sourceParts[0];
        $sourceRemaining = $sourceParts[1] ?? null;

        $targetBase = $targetParts[0];
        $targetRemaining = $targetParts[1] ?? null;

        // Get the collection (e.g., product_variation_show)
        $collection = $this->getNestedValue($data, $sourceBase, false);

        if (!is_iterable($collection)) {
            return;
        }

        foreach ($collection as $index => $item) {
            if ($sourceRemaining && str_contains($sourceRemaining, '.*.')) {
                // There's another wildcard deeper
                $newTarget = $targetBase . '.' . $index . '.' . $targetRemaining;
                $this->handleWildcardMapping($item, $mappedData, $sourceRemaining, $newTarget, $formatValues, $numericKeys, $isArrayTarget);
            } else {
                // No more wildcards - leaf field
                $val = $this->getNestedValue($item, $sourceRemaining, $formatValues);
                $fullPath = $targetBase . '.' . $index . ($targetRemaining ? '.' . $targetRemaining : '');
                $cleanVal = $this->applyFieldConstraints($fullPath, $val, $numericKeys);

                if ($isArrayTarget && is_array($cleanVal)) {
                    foreach ($cleanVal as $arrayItem) {
                        $this->setNestedValue($mappedData, $fullPath . '[]', $arrayItem);
                    }
                } else {
                    $this->setNestedValue($mappedData, $fullPath, $cleanVal);
                }
            }
        }
    }

    private function applyFieldConstraints(string $target, mixed $value, array $numericKeys = []): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->applyFieldConstraints($target, $item, $numericKeys), $value);
        }

        if (!is_string($value) || empty($value)) {
            return $value;
        }
        return $this->isFormattedNumeric($value) ? $this->stripFormatting($value) : $value;
    }

    private function stripFormatting(mixed $value): mixed
    {
        if (!is_string($value) || empty($value)) {
            return $value;
        }

        if (preg_match('/[a-zA-Z\/]/', $value)) {
            return $value;
        }

        $clean = preg_replace('/[^\d,.-]/', '', $value);

        if (empty($clean) || !is_numeric(str_replace([',', '.'], '', $clean))) {
            return $value;
        }

        if (str_contains($clean, '.') && str_contains($clean, ',')) {
            $dotPos = strrpos($clean, '.');
            $commaPos = strrpos($clean, ',');
            if ($dotPos < $commaPos) {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                $clean = str_replace(',', '', $clean);
            }
        } elseif (str_contains($clean, ',')) {
            $clean = str_replace(',', '.', $clean);
        }

        return (string) (float) $clean;
    }

    private function isFormattedNumeric(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Exclude common path/non-numeric patterns immediately
        if (preg_match('/[a-zA-Z\/]/', $value)) {
            return false;
        }

        $hasDigits = preg_match('/\d/', $value);
        $hasFormatting = preg_match('/[,.]|^\d{1,3}(?:[.,]\d{3})*(?:[.,]\d+)?$/', $value);
        $isPricePattern = preg_match('/^[\d.,\s$€£¥]+$/', $value);

        return $hasDigits && ($hasFormatting || $isPricePattern);
    }

    private function getNestedValue(mixed $current, string $path, bool $formatValues): mixed
    {
        if ($current === null) {
            return null;
        }

        $keys = explode('.', $path);
        $lastReflectionProp = null;

        foreach ($keys as $index => $key) {
            $isLastKey = ($index === count($keys) - 1);

            // Auto-decode JSON column strings
            if (is_string($current) && !empty($current)) {
                $trimmed = trim($current);
                if (str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[')) {
                    $decoded = json_decode($current, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $current = $decoded;
                    }
                }
            }

            if ($current instanceof Entity) {
                $camelKey = StringUtils::snakeCaseToCamelCase($key);

                if ($current->hasProperty($camelKey)) {
                    $lastReflectionProp = $current->getProperty($camelKey);
                } elseif ($current->hasProperty($key)) {
                    $lastReflectionProp = $current->getProperty($key);
                }
                $value = $current->getFieldValue($camelKey);

                if ($value === null) {
                    $value = $current->getFieldValue($key);
                }

                $current = $value;
            } elseif (is_object($current)) {
                $camelKey = StringUtils::snakeCaseToCamelCase($key);

                $getter = 'get' . ucfirst($camelKey);
                if (method_exists($current, $getter)) {
                    $current = $current->$getter();
                } elseif (property_exists($current, $camelKey)) {
                    $current = $current->$camelKey;
                } elseif (property_exists($current, $key)) {
                    $current = $current->$key;
                } else {
                    return null;
                }

                if ($isLastKey && $formatValues && is_object($current)) {
                    return $this->typePresenterFactory->displayValue($current);
                }
            } elseif (is_array($current)) {
                // Handle array access with numeric keys
                if (is_numeric($key) && isset($current[$key])) {
                    $current = $current[$key];
                } else {
                    $current = $current[$key] ?? $current[StringUtils::snakeCaseToCamelCase($key)] ?? null;
                }
            } elseif (is_scalar($current)) {
                break;
            } else {
                return null;
            }

            if ($current === null) {
                return null;
            }
        }

        // Format the final value if needed
        if ($formatValues && $current !== null) {
            if ($lastReflectionProp !== null) {
                $formattedValue = $this->typePresenterFactory->displayValue($current, $lastReflectionProp);
                return $this->autoCleanNumericValue($formattedValue);
            }
            if (is_object($current)) {
                return $this->typePresenterFactory->displayValue($current);
            }
            return $this->autoCleanNumericValue($current);
        }

        return $current;
    }

    private function autoCleanNumericValue(mixed $value): mixed
    {
        if (!is_string($value) || !$this->isFormattedNumeric($value)) {
            return $value;
        }

        return $this->stripFormatting($value);
    }

    private function setNestedValue(array &$array, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$array;

        foreach ($keys as $key) {
            // Handle array syntax with [] at the end of the key safely
            $isArrayPush = str_ends_with($key, '[]');

            // FIX: Only strip the exact trailing '[]' substring, leaving single brackets like ']' untouched
            $cleanKey = $isArrayPush ? substr($key, 0, -2) : $key;

            if ($isArrayPush) {
                if (!isset($current[$cleanKey]) || !is_array($current[$cleanKey])) {
                    $current[$cleanKey] = [];
                }
                $current[$cleanKey][] = $value;
                return;
            }

            if (!isset($current[$cleanKey]) || !is_array($current[$cleanKey])) {
                $current[$cleanKey] = [];
            }
            $current = &$current[$cleanKey];
        }

        if ($keys !== []) {
            $current = $value;
        }
    }

    // private function setNestedValue(array &$array, string $path, mixed $value): void
    // {
    //     $keys = explode('.', $path);
    //     $current = &$array;

    //     foreach ($keys as $key) {
    //         // Handle array syntax with [] at the end of the key
    //         $isArrayPush = str_ends_with($key, '[]');
    //         $cleanKey = rtrim($key, '[]');

    //         if ($isArrayPush) {
    //             if (!isset($current[$cleanKey]) || !is_array($current[$cleanKey])) {
    //                 $current[$cleanKey] = [];
    //             }
    //             $current[$cleanKey][] = $value;
    //             return;
    //         }

    //         if (!isset($current[$cleanKey]) || !is_array($current[$cleanKey])) {
    //             $current[$cleanKey] = [];
    //         }
    //         $current = &$current[$cleanKey];
    //     }

    //     if ($keys !== []) {
    //         $current = $value;
    //     }
    // }
}