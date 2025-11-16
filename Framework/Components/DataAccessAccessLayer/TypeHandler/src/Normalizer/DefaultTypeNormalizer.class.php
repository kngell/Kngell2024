<?php

declare(strict_types=1);

final class DefaultTypeNormalizer implements TypeNormalizerInterface
{
    public function __construct(private TypeHandlerFactory $factory)
    {
    }

    public function normalizeForDatabaseToEntity(mixed $rawValue, ReflectionProperty $property, object $entityInstance): mixed
    {
        $handler = $this->factory->getHandlerForValue($rawValue, $property);
        return $handler->normalizeForEntity($rawValue, $property, $entityInstance);
    }

    public function normalizeForClientToEntity(mixed $clientValue, ReflectionProperty $property, object $entityInstance): mixed
    {
        $handler = $this->factory->getHandlerForValue($clientValue, $property);
        return $handler->normalizeForEntity($clientValue, $property, $entityInstance);
    }

    public function normalizeForEntityToDatabase(mixed $entityValue, ReflectionProperty $property): mixed
    {
        if ($entityValue === null) {
            return null;
        }

        $handler = $this->factory->getHandlerForValue($entityValue, $property);
        return $handler->normalizeForDatabase($entityValue);
    }

    public function normalizeValuesForDatabase(array $values, object $entity): array
    {
        $reflection = new ReflectionClass($entity);
        $normalizedValues = [];

        foreach ($values as $dbFieldName => $entityPropertyValue) {
            $propertyName = StringUtils::camelCase($dbFieldName);

            if ($reflection->hasProperty($propertyName)) {
                try {
                    $property = $reflection->getProperty($propertyName);
                    $normalizedValues[$dbFieldName] = $this->normalizeForEntityToDatabase(
                        $entityPropertyValue,
                        $property,
                    );
                } catch (ReflectionException $e) {
                    $normalizedValues[$dbFieldName] = $entityPropertyValue;
                }
            } else {
                $normalizedValues[$dbFieldName] = $entityPropertyValue;
            }
        }
        return $normalizedValues;
    }

    public function normalizeValueForDatabase(string $dbFieldName, mixed $rawValue, ?object $entity): mixed
    {
        if ($entity) {
            $reflection = new ReflectionClass($entity);
            $propertyName = StringUtils::camelCase($dbFieldName);

            if ($reflection->hasProperty($propertyName)) {
                try {
                    $property = $reflection->getProperty($propertyName);
                    return $this->normalizeForEntityToDatabase($rawValue, $property);
                } catch (ReflectionException $e) {
                    // Fall through to generic handling
                }
            }
        }

        // Generic handling without property context
        $handler = $this->factory->getHandlerForValue($rawValue, null);
        return $handler->normalizeForDatabase($rawValue);
    }

    public function normalizeQueryValuesForDatabase(array $values, array $fieldTypes = []): array
    {
        $normalized = [];

        foreach ($values as $field => $value) {
            $fieldType = $fieldTypes[$field] ?? null;
            $normalized[$field] = $this->normalizeQueryValueForDatabase($value, $fieldType);
        }

        return $normalized;
    }

    public function normalizeQueryValueForDatabase(mixed $value, ?string $fieldType = null): mixed
    {
        $handler = $this->factory->getHandlerForQueryValue($value, $fieldType);
        return $handler->normalizeForDatabase($value);
    }
}