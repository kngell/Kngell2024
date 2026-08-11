<?php

declare(strict_types=1);

final class BooleanType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_bool($value) || is_string($value) || is_numeric($value);
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, ?Entity $entity): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(trim($value));
            return in_array($value, ['1', 'true', 'yes', 'y', 'on', 'active']);
        }

        return (bool) $value;
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value ? 1 : 0;
    }
}