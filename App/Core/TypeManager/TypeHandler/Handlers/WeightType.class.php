<?php

declare(strict_types=1);

class WeightType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof Weight
            || is_numeric($value)
            || (is_string($value) && $this->isValidWeightString($value))
            || (is_array($value) && $this->isValidWeightArray($value));
    }

    public function normalizeForEntity(mixed $rawValue, ReflectionProperty $property, object $contextEntity): mixed
    {
        // Handle null/empty values
        if ($rawValue === null || $rawValue === '') {
            return null;
        }

        // Already a Weight object
        if ($rawValue instanceof Weight) {
            return $rawValue;
        }

        // Handle numeric values (assumed kilograms)
        if (is_numeric($rawValue)) {
            return Weight::fromKilograms((float) $rawValue);
        }

        // Handle string values
        if (is_string($rawValue)) {
            return $this->parseWeightString($rawValue);
        }

        // Handle arrays
        if (is_array($rawValue)) {
            return Weight::fromArray($rawValue);
        }

        throw new InvalidArgumentException(sprintf(
            'Cannot normalize value of type %s to Weight. Received: %s',
            gettype($rawValue),
            var_export($rawValue, true),
        ));
    }

    public function normalizeForDatabase(mixed $entityValue, ?ReflectionProperty $property = null): mixed
    {
        if ($entityValue === null) {
            return null;
        }

        if (!$entityValue instanceof Weight) {
            throw new InvalidArgumentException(
                'Expected Weight instance for database storage, got: ' . gettype($entityValue),
            );
        }

        // Return the database representation (kilograms as float)
        return $entityValue->getDatabaseValue();
    }

    private function parseWeightString(string $weightString): Weight
    {
        $weightString = trim($weightString);

        // Empty string
        if ($weightString === '') {
            throw new InvalidArgumentException('Weight string cannot be empty');
        }

        // Simple numeric string (assume kilograms)
        if (is_numeric($weightString)) {
            return Weight::fromKilograms((float) $weightString);
        }

        // Try to parse with units using your WeightUnits enum
        if (preg_match('/^([0-9.]+)\s*(kg|g|lb|oz|kilograms?|grams?|pounds?|ounces?)?$/i', $weightString, $matches)) {
            $value = (float) $matches[1];
            $unitStr = strtolower($matches[2] ?? 'kg');

            // Map to your WeightUnits enum values
            $unit = match(true) {
                $unitStr === 'kg' || str_starts_with($unitStr, 'kilogram') => WeightUnits::KILOGRAM,
                $unitStr === 'g' || str_starts_with($unitStr, 'gram') => WeightUnits::GRAM,
                $unitStr === 'lb' || str_starts_with($unitStr, 'pound') => WeightUnits::POUND,
                $unitStr === 'oz' || str_starts_with($unitStr, 'ounce') => WeightUnits::OUNCE,
                default => WeightUnits::KILOGRAM
            };

            return new Weight($value, $unit);
        }

        throw new InvalidArgumentException(
            "Invalid weight format: '{$weightString}'. " .
            "Expected formats: '1.5', '1.5 kg', '1500 g', '3.3 lb'",
        );
    }

    private function isValidWeightString(string $value): bool
    {
        $value = trim($value);
        if ($value === '') {
            return false;
        }

        // Check if it's numeric
        if (is_numeric($value)) {
            return true;
        }

        // Check if it matches weight pattern with units
        return (bool) preg_match('/^[0-9.]+\s*(kg|g|lb|oz|kilograms?|grams?|pounds?|ounces?)?$/i', $value);
    }

    private function isValidWeightArray(array $value): bool
    {
        // Check if it has the structure expected by Weight::fromArray()
        return isset($value['value']) && is_numeric($value['value']) ||
               isset($value['kilograms']) && is_numeric($value['kilograms']);
    }
}