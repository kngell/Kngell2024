<?php

declare(strict_types=1);

class PriceRangeType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        if ($property !== null) {
            $propertyType = $property->getType();
            if ($propertyType instanceof ReflectionNamedType && $propertyType->getName() === PriceRange::class) {
                return true;
            }
        }

        return $value instanceof PriceRange;
    }

    public function normalizeForEntity(
        mixed $rawValue,
        ReflectionProperty $property,
        object $contextEntity,
    ): mixed {
        // Handle null
        if ($rawValue === null || $rawValue === '') {
            return null;
        }

        // Already a PriceRange object
        if ($rawValue instanceof PriceRange) {
            return $rawValue;
        }

        // Handle array from form submission
        if (is_array($rawValue) && isset($rawValue['brackets'])) {
            return PriceRange::fromArray($rawValue);
        }

        // Handle JSON string from database
        if (is_string($rawValue)) {
            $decoded = json_decode($rawValue, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($decoded['brackets'])) {
                return PriceRange::fromArray($decoded);
            }
        }

        // If we have min/max from the entity, generate default ranges
        if ($contextEntity instanceof Category) {
            $minPrice = $contextEntity->getMinPrice();
            $maxPrice = $contextEntity->getMaxPrice();

            if ($minPrice && $maxPrice) {
                return PriceRange::fromMinMax($minPrice, $maxPrice);
            }
        }

        return null;
    }

    public function normalizeForDatabase(mixed $entityValue, ?ReflectionProperty $property = null): mixed
    {
        if ($entityValue === null) {
            return null;
        }

        if (!$entityValue instanceof PriceRange) {
            throw new InvalidArgumentException('Expected PriceRange instance');
        }

        return json_encode($entityValue->toArray());
    }
}