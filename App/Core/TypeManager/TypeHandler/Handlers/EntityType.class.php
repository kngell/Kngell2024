<?php

declare(strict_types=1);

final class EntityType implements TypeHandlerInterface
{
    public function __construct(
        // private EntityFactory $entityFactory,
    ) {
    }

    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        // With property context - check if property type is an Entity class
        if ($property !== null) {
            $propertyType = $property->getType();
            if ($propertyType instanceof ReflectionNamedType) {
                $typeName = $propertyType->getName();
                return is_subclass_of($typeName, Entity::class) || $typeName === Entity::class;
            }
            return false;
        }

        // Without property context - check if value is an Entity instance
        return $value instanceof Entity;
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): mixed
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof Entity) {
            throw new InvalidArgumentException(
                'Cannot normalize non-entity value for database: ' . gettype($value),
            );
        }

        // For database storage, we need the foreign key ID
        return $value; //$value->getProperty($value->getEntityKeyField());
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, object $contextEntity): mixed
    {
        if ($value === null || $value === '') {
            // Return null if property allows null
            if ($property->getType()?->allowsNull()) {
                return null;
            }
            throw new InvalidArgumentException(
                "Cannot convert null to non-nullable entity for property {$property->getName()}",
            );
        }

        // If value is already the correct entity instance
        $expectedEntityClass = $property->getType()?->getName();
        if ($value instanceof $expectedEntityClass) {
            return $value;
        }

        // If value is an array of data

        throw new InvalidArgumentException(
            'Cannot convert value of type ' . gettype($value) .
            " to entity {$expectedEntityClass} for property {$property->getName()}",
        );
    }
}