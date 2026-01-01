<?php

declare(strict_types=1);

class EmptyStringType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value === '';
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): mixed
    {
        return null;
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, object $contextEntity): mixed
    {
        $propertyType = $property->getType();

        if ($propertyType?->allowsNull()) {
            return null;
        }

        if ($propertyType instanceof ReflectionNamedType) {
            return match ($propertyType->getName()) {
                'int', 'integer' => 0,
                'float', 'double' => 0.0,
                'bool', 'boolean' => false,
                default => '',
            };
        }

        return '';
    }
}