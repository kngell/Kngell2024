<?php

declare(strict_types=1);

class StandardType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_scalar($value); // No need to handle null anymore!
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

        return match ($targetType) {
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value,
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'string' => (string) $value,
            default => $value,
        };
    }
}