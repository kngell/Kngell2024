<?php

declare(strict_types=1);

class StandardType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_scalar($value);
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): mixed
    {
        return match (true) {
            is_bool($value) => $value ? 1 : 0,
            is_float($value) => (float) $value,
            is_int($value) => (int) $value,
            default => $value,
        };
    }

    public function normalizeForEntity(
        mixed $value,
        ReflectionProperty $property,
        object $contextEntity,
    ): mixed {
        $propertyType = $property->getType();
        $targetType = $propertyType instanceof ReflectionNamedType ? $propertyType->getName() : 'mixed';

        if ($value === '' || $value === null) {
            if ($propertyType?->allowsNull()) {
                return null;
            }
            return match ($targetType) {
                'int', 'integer' => 0,
                'float', 'double' => 0.0,
                'bool', 'boolean' => false,
                default => '',
            };
        }

        if (($targetType === 'int' || $targetType === 'integer') && is_string($value)) {
            $value = preg_replace('/[^\d-]/', '', $value);
        }
        return match ($targetType) {
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value,
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'string' => (string) $value,
            default => $value,
        };
    }
}