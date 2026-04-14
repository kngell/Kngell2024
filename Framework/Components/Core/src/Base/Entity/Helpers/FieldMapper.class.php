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
                $value = $this->applyFieldConstraints(
                    $targetPath,
                    $value,
                    $mapping !== null ? $mapping->getNumericFields() : [],
                );
                $this->setNestedValue($mappedData, $targetPath, $value);
            }
        }

        return $mappedData;
    }

    private function handleWildcardMapping(Entity $data, array &$mappedData, string $source, string $target, bool $formatValues, array $numericKeys = []): void
    {
        $sourceParts = explode('.*.', $source, 2);
        $targetParts = explode('.*.', $target, 2);

        $sourceBase = $sourceParts[0];
        $sourceRemaining = $sourceParts[1] ?? null;

        $targetBase = $targetParts[0];
        $targetRemaining = $targetParts[1] ?? null;

        $collection = $this->getNestedValue($data, $sourceBase, false);
        if (!is_iterable($collection)) {
            return;
        }

        foreach ($collection as $index => $item) {
            if ($sourceRemaining && str_contains($sourceRemaining, '.*.')) {
                $currentTargetPath = $targetBase . '.' . $index;
                $existingData = $this->getExistingNestedArray($mappedData, $currentTargetPath);
                $this->handleWildcardMapping($item, $existingData, $sourceRemaining, $targetRemaining, $formatValues);
                $this->setNestedValue($mappedData, $currentTargetPath, $existingData);
            } else {
                $val = $this->getNestedValue($item, $sourceRemaining, $formatValues);
                if ($targetRemaining !== null && str_ends_with($targetRemaining, '[]')) {
                    $cleanTarget = rtrim($targetRemaining, '[]');
                    $fullPath = $targetBase . ($cleanTarget ? '.' . $cleanTarget : '');
                    if (!isset($mappedData[$fullPath])) {
                        $mappedData[$fullPath] = [];
                    }

                    if ($val !== null) {
                        $mappedData[$fullPath][] = $val;
                    }
                } else {
                    $remaining = $targetRemaining ?? '';
                    $fullPath = $targetBase . '.' . $index . ($remaining !== '' ? '.' . $remaining : '');

                    $cleanVal = $this->applyFieldConstraints($fullPath, $val, $numericKeys);
                    $this->setNestedValue($mappedData, $fullPath, $cleanVal);
                }
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
        $sanitizedKeywords = array_filter(array_map('trim', $numericKeys));
        $isNumericTarget = false;
        $matchedKeyword = '';

        foreach ($sanitizedKeywords as $keyword) {
            if (str_contains($target, $keyword)) {
                $isNumericTarget = true;
                $matchedKeyword = $keyword;
                break;
            }
        }

        if ($isNumericTarget && $this->isFormattedNumeric($value)) {
            return $this->stripFormatting($value, $matchedKeyword);
        }

        return $value;
    }

    private function stripFormatting(mixed $value, string $keyword = ''): mixed
    {
        if (!is_string($value) || empty($value)) {
            return $value;
        }

        // 🎯 Rule: Quantities are Integers. No dots, no commas.
        if ($keyword === 'quantity' || str_contains($keyword, 'quantity')) {
            return preg_replace('/[^\d]/', '', $value);
        }

        // Existing logic for prices/modifiers (allowing decimals)
        $clean = preg_replace('/[^\d,.]/', '', $value);

        // Normalize logic for decimals...
        if (str_contains($clean, '.') && str_contains($clean, ',')) {
            $dotPos = strrpos($clean, '.');
            $commaPos = strrpos($clean, ',');
            if ($dotPos > $commaPos) {
                $clean = str_replace(',', '', $clean);
            } else {
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            }
        } elseif (str_contains($clean, ',')) {
            $clean = str_replace(',', '.', $clean);
        }

        return $clean;
    }

    private function isFormattedNumeric(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        return preg_match('/^[^\d]*[\d,.]+[^\d]*$/', $value) === 1;
    }

    private function getNestedValue(mixed $current, string $path, bool $formatValues): mixed
    {
        if ($current === null) {
            return null;
        }

        $keys = explode('.', $path);
        $lastReflectionProp = null;

        foreach ($keys as $key) {
            if ($current instanceof Entity) {
                $camelKey = StringUtils::snakeCaseToCamelCase($key);

                if ($current->hasProperty($camelKey)) {
                    $lastReflectionProp = $current->getProperty($camelKey);
                } elseif ($current->hasProperty($key)) {
                    $lastReflectionProp = $current->getProperty($key);
                }
                $value = $current->getFieldValue($camelKey);

                // If null, fallback to the raw key
                if ($value === null) {
                    $value = $current->getFieldValue($key);
                }

                $current = $value;
            } elseif (is_array($current)) {
                $current = $current[$key] ?? $current[StringUtils::snakeCaseToCamelCase($key)] ?? null;
                $lastReflectionProp = null;
            } else {
                return null;
            }

            if ($current === null) {
                return null;
            }
        }

        if ($formatValues && $lastReflectionProp !== null && $current !== null) {
            return $this->typePresenterFactory->displayValue($current, $lastReflectionProp);
        }

        return $current;
    }

    private function setNestedValue(array &$array, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$array;

        foreach ($keys as $key) {
            // If the segment doesn't exist, initialize it as an array
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        // Now $current is a reference to the leaf location
        $current = $value;
    }
}
