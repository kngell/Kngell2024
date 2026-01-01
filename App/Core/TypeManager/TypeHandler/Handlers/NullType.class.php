<?php

declare(strict_types=1);

class NullType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value === null;
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): mixed
    {
        return null;
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, object $contextEntity): mixed
    {
        $propertyType = $property->getType();

        // If property allows null, return null
        if ($propertyType?->allowsNull()) {
            return null;
        }

        // For non-nullable properties, provide sensible defaults
        if ($propertyType instanceof ReflectionNamedType) {
            return match($propertyType->getName()) {
                'string' => '',
                'int','integer' => 0,
                'float' => 0.0,
                'bool' => false,
                'array' => [],
                default => throw new InvalidArgumentException(
                    "Cannot convert null to non-nullable type {$propertyType->getName()} for property {$property->getName()}",
                )
            };
        }

        throw new InvalidArgumentException(
            "Cannot convert null for property {$property->getName()} with undefined type",
        );
    }
}