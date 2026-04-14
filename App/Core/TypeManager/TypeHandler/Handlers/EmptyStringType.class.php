<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class EmptyStringType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value === '';
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): mixed
    {
        return null; // Empty string becomes NULL in database
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, object $contextEntity): mixed
    {
        $propertyType = $property->getType();

        if ($propertyType?->allowsNull()) {
            return null;
        }

        if ($propertyType instanceof ReflectionNamedType) {
            $typeName = $propertyType->getName();

            if ($typeName === UuidInterface::class || is_subclass_of($typeName, UuidInterface::class)) {
                return Uuid::uuid4();
            }

            // Handle other types
            return match ($typeName) {
                'int', 'integer' => 0,
                'float', 'double' => 0.0,
                'bool', 'boolean' => false,
                'string' => '',
                'array' => [],
                default => null,
            };
        }

        // Handle union types
        if ($propertyType instanceof ReflectionUnionType) {
            // Check if UUID is in the union
            foreach ($propertyType->getTypes() as $type) {
                if ($type instanceof ReflectionNamedType) {
                    if ($type->getName() === UuidInterface::class ||
                        is_subclass_of($type->getName(), UuidInterface::class)) {
                        // If UUID is in union and value is empty, generate UUID
                        return Uuid::uuid4();
                    }
                }
            }

            // If any type allows null, return null
            if ($propertyType->allowsNull()) {
                return null;
            }
        }

        // Default fallback - generate UUID for safety
        if (str_contains($property->getDocComment() ?? '', 'UuidInterface')) {
            return Uuid::uuid4();
        }

        return null;
    }
}