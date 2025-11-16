<?php

declare(strict_types=1);

final class DimensionsType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return $value instanceof Dimensions;
    }

    public function normalizeForEntity(mixed $rawValue, ReflectionProperty $property, object $entityInstance): mixed
    {
        // Handle JSON string from database
        if (is_string($rawValue)) {
            $data = json_decode($rawValue, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return Dimensions::fromArray($data);
            }
        }

        // Handle array from database or client
        if (is_array($rawValue)) {
            return Dimensions::fromArray($rawValue);
        }

        // Already a Dimensions object
        if ($rawValue instanceof Dimensions) {
            return $rawValue;
        }

        throw new InvalidArgumentException(sprintf(
            'Cannot normalize value of type %s to Dimensions',
            gettype($rawValue),
        ));
    }

    public function normalizeForDatabase(mixed $entityValue): mixed
    {
        if (!$entityValue instanceof Dimensions) {
            throw new InvalidArgumentException('Expected Dimensions instance');
        }

        // Store as JSON in database
        return json_encode([
            'length' => $entityValue->getLength(),
            'width' => $entityValue->getWidth(),
            'height' => $entityValue->getHeight(),
            'unit' => $entityValue->getUnit()->value,
        ], JSON_PRESERVE_ZERO_FRACTION);
    }

    public function getSupportedType(): ?string
    {
        return Dimensions::class;
    }
}