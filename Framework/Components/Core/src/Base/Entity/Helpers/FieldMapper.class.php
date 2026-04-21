<?php

declare(strict_types=1);
final readonly class FieldMapper
{
    public function __construct(
        private TypePresenterFactory $typePresenterFactory,
    ) {
    }

    public function applyMapping(Entity $source, ?FormFieldMappingPayloadInterface $mapping = null, bool $formatValues = true): array
    {
        $mappedData = [];
        foreach ($mapping->getFieldMapping() as $sourcePath => $targetPath) {
            if (str_contains($sourcePath, '.*.')) {
                $this->handleWildcardMapping($source, $mappedData, $sourcePath, $targetPath, $formatValues, $mapping->getNumericFields());
            } else {
                $value = $this->getNestedValue($source, $sourcePath, $formatValues);
                $value = $this->applyFieldConstraints($targetPath, $value, $mapping?->getNumericFields() ?? []);
                $this->setNestedValue($mappedData, $targetPath, $value);
            }
        }
        return $mappedData;
    }

    private function handleWildcardMapping(Entity|array $data, array &$mappedData, string $source, string $target, bool $formatValues, array $numericKeys = []): void
    {
        $sourceParts = explode('.*.', $source, 2);
        $targetParts = explode('.*.', $target, 2);

        $sourceBase = $sourceParts[0];
        $sourceRemaining = $sourceParts[1] ?? null;

        $targetBase = $targetParts[0];
        $targetRemaining = $targetParts[1] ?? null;

        // We pass false to formatValues here because we want the raw array/collection to loop over
        $collection = $this->getNestedValue($data, $sourceBase, false);

        if (!is_iterable($collection)) {
            return;
        }

        foreach ($collection as $index => $item) {
            if ($sourceRemaining && str_contains($sourceRemaining, '.*.')) {
                // Recurse for nested wildcards (like variation_attribute.*.id)
                $this->handleWildcardMapping($item, $mappedData, $sourceRemaining, $targetRemaining, $formatValues, $numericKeys);
            } else {
                // We are at the leaf (e.g., label, min, max)
                $val = $this->getNestedValue($item, $sourceRemaining, $formatValues);

                // Build the target path: price_ranges.brackets.0.label
                $fullPath = $targetBase . '.' . $index . ($targetRemaining ? '.' . $targetRemaining : '');

                $cleanVal = $this->applyFieldConstraints($fullPath, $val, $numericKeys);
                $this->setNestedValue($mappedData, $fullPath, $cleanVal);
            }
        }
    }

    private function getExistingNestedArray(array $data, string $path): array
    {
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (!isset($data[$key]) || !is_array($data[$key])) {
                return [];
            }
            $data = $data[$key];
        }
        return $data;
    }

    private function applyFieldConstraints(string $target, mixed $value, array $numericKeys = []): mixed
    {
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

        // HEURISTIC: If it contains directory separators or letters, it's likely a path/string
        if (preg_match('/[a-zA-Z\/]/', $value)) {
            return $value;
        }

        $clean = preg_replace('/[^\d,.-]/', '', $value);

        // Final sanity check: if the result is empty or not numeric, return original
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
            }
            // Handle Value Objects - similar to Entity but using getters
            elseif (is_object($current)) {
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
                $current = $current[$key] ?? $current[StringUtils::snakeCaseToCamelCase($key)] ?? null;
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
                // Auto-clean numeric strings
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
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }
        $current = $value;
    }
}