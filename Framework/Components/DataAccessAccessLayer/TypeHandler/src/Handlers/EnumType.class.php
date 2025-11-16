<?php

declare(strict_types=1);

final class EnumType implements TypeHandlerInterface
{
    // public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    // {
    //     // We need property context to detect enums
    //     if ($property === null) {
    //         return false;
    //     }

    //     $propertyType = $property->getType();
    //     if (!$propertyType instanceof ReflectionNamedType) {
    //         return false;
    //     }

    //     $typeName = $propertyType->getName();
    //     return enum_exists($typeName);
    // }
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        // With property context - check if property is enum
        if ($property !== null) {
            $propertyType = $property->getType();
            if ($propertyType instanceof ReflectionNamedType) {
                return enum_exists($propertyType->getName());
            }
            return false;
        }

        // Without property context - check if value is enum instance
        return $value instanceof BackedEnum || $value instanceof UnitEnum;
    }

    public function normalizeForDatabase(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        // Handle string/int values that might be enum representations
        if (is_scalar($value)) {
            return $value;
        }

        throw new InvalidArgumentException(
            'Cannot normalize non-enum value for database: ' . gettype($value),
        );
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, object $contextEntity): mixed
    {
        $enumClass = $property->getType()?->getName();

        if (!$enumClass || !enum_exists($enumClass)) {
            throw new InvalidArgumentException(
                "Enum class '{$enumClass}' is missing or invalid for property {$property->getName()}",
            );
        }

        // 1. Handle already correct enum instance
        if ($value instanceof $enumClass) {
            return $value;
        }

        // 2. Handle null values
        if ($value === null) {
            return $this->handleNullValue($property, $enumClass);
        }

        // 3. Handle backed enums
        $reflection = new ReflectionEnum($enumClass);
        if ($reflection->getBackingType() !== null) {
            return $this->handleBackedEnum($value, $enumClass, $property);
        }

        // 4. Handle pure enums (no backing type)
        return $this->handlePureEnum($value, $enumClass, $property);
    }

    /**
     * Additional utility methods for better enum handling.
     */
    public function validateValue(mixed $value, ReflectionProperty $property): bool
    {
        if (!$this->supports($value, $property)) {
            return false;
        }

        $enumClass = $property->getType()?->getName();
        if (!$enumClass) {
            return false;
        }

        try {
            $this->normalizeForEntity($value, $property, new stdClass());
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function getDefaultValue(?ReflectionProperty $property = null): mixed
    {
        if ($property === null) {
            return null;
        }

        $enumClass = $property->getType()?->getName();
        if (!$enumClass || !enum_exists($enumClass)) {
            return null;
        }

        $cases = $enumClass::cases();
        return $cases[0] ?? null;
    }

    private function handleNullValue(ReflectionProperty $property, string $enumClass): ?object
    {
        if ($property->getType()?->allowsNull()) {
            return null;
        }

        throw new InvalidArgumentException(
            "Cannot convert null to non-nullable enum {$enumClass} for property {$property->getName()}",
        );
    }

    private function handleBackedEnum(mixed $value, string $enumClass, ReflectionProperty $property): object
    {
        $reflection = new ReflectionEnum($enumClass);
        $backingType = $reflection->getBackingType()?->getName();

        try {
            // Convert value to match backing type
            $convertedValue = $this->convertToBackingType($value, $backingType);

            return $enumClass::from($convertedValue);
        } catch (ValueError $e) {
            // Try case-insensitive matching for string-backed enums
            if ($backingType === 'string' && is_scalar($value)) {
                $result = $this->tryCaseInsensitiveMatch($enumClass, (string) $value);
                if ($result !== null) {
                    return $result;
                }
            }

            throw new InvalidArgumentException(
                "Invalid value '{$value}' for backed enum {$enumClass}. " .
                "Expected {$backingType} value, got " . gettype($value) . '. ' .
                'Valid values: ' . $this->getValidValuesString($enumClass),
                0,
                $e,
            );
        }
    }

    private function handlePureEnum(mixed $value, string $enumClass, ReflectionProperty $property): object
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                "Invalid value for pure enum {$enumClass}. " .
                'Expected string case name, got ' . gettype($value) . '. ' .
                'Valid cases: ' . $this->getValidCasesString($enumClass),
            );
        }

        foreach ($enumClass::cases() as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        // Try case-insensitive matching
        $result = $this->tryCaseInsensitiveMatch($enumClass, $value);
        if ($result !== null) {
            return $result;
        }

        throw new InvalidArgumentException(
            "Invalid case name '{$value}' for pure enum {$enumClass}. " .
            'Valid cases: ' . $this->getValidCasesString($enumClass),
        );
    }

    private function convertToBackingType(mixed $value, ?string $backingType): mixed
    {
        if ($backingType === null) {
            return $value;
        }

        return match ($backingType) {
            'string' => $this->safeStringConversion($value),
            'int' => $this->safeIntConversion($value),
            default => $value
        };
    }

    private function safeStringConversion(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_numeric($value) || is_bool($value)) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        throw new InvalidArgumentException(
            'Cannot convert ' . gettype($value) . ' to string for enum backing value',
        );
    }

    private function safeIntConversion(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value) && (string) (int) $value === (string) $value) {
            return (int) $value;
        }

        throw new InvalidArgumentException(
            'Cannot convert ' . gettype($value) . ' to int for enum backing value',
        );
    }

    private function tryCaseInsensitiveMatch(string $enumClass, string $value): ?object
    {
        $normalizedValue = strtolower(trim($value));

        foreach ($enumClass::cases() as $case) {
            // For backed enums, check both name and value
            if ($case instanceof BackedEnum) {
                if (strtolower($case->name) === $normalizedValue ||
                    strtolower((string) $case->value) === $normalizedValue) {
                    return $case;
                }
            } else {
                // For pure enums, check only name
                if (strtolower($case->name) === $normalizedValue) {
                    return $case;
                }
            }
        }

        return null;
    }

    private function getValidValuesString(string $enumClass): string
    {
        $values = [];
        foreach ($enumClass::cases() as $case) {
            if ($case instanceof BackedEnum) {
                $values[] = "{$case->name} ({$case->value})";
            } else {
                $values[] = $case->name;
            }
        }

        return implode(', ', $values);
    }

    private function getValidCasesString(string $enumClass): string
    {
        $cases = array_map(fn ($case) => $case->name, $enumClass::cases());
        return implode(', ', $cases);
    }
}