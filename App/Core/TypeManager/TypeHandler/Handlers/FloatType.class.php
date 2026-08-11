<?php

declare(strict_types=1);

final class FloatType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_float($value) || is_numeric($value) || is_string($value);
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, ?Entity $entity): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_float($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $cleaned = preg_replace('/[^0-9.,-]/', '', $value);
            $cleaned = str_replace(',', '.', $cleaned);
            return $cleaned !== '' ? (float) $cleaned : null;
        }

        return null;
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): ?float
    {
        return $value !== null ? (float) $value : null;
    }
}