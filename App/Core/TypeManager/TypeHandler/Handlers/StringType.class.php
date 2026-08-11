<?php

declare(strict_types=1);

final class StringType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_string($value) || is_scalar($value);
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, ?Entity $entity): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value;
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): ?string
    {
        if ($value === null) {
            return null;
        }

        return (string) $value;
    }
}