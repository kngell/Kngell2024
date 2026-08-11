<?php

declare(strict_types=1);

final class IntegerType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_int($value) || is_numeric($value) || is_string($value);
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, ?Entity $entity): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $cleaned = preg_replace('/[^0-9-]/', '', $value);
            return $cleaned !== '' ? (int) $cleaned : null;
        }

        return null;
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): ?int
    {
        return $value !== null ? (int) $value : null;
    }
}