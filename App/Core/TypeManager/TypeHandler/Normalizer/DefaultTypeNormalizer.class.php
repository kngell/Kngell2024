<?php

declare(strict_types=1);

final class DefaultTypeNormalizer implements TypeNormalizerInterface
{
    public function __construct(private TypeHandlerFactory $factory, private bool $strictMode = false)
    {
    }

    public function normalizeFromDatabaseToEntity(mixed $rawValue, ReflectionProperty $property, Entity $entityInstance): mixed
    {
        $handler = $this->factory->getHandlerForValue($rawValue, $property);
        return $handler->normalizeForEntity($rawValue, $property, $entityInstance);
    }

    public function normalizeForClientToEntity(mixed $clientValue, ReflectionProperty $property, Entity $entityInstance): mixed
    {
        $handler = $this->factory->getHandlerForValue($clientValue, $property);
        return $handler->normalizeForEntity($clientValue, $property, $entityInstance);
    }

    public function normalizeFromEntityToDatabase(mixed $entityValue, ReflectionProperty $property): mixed
    {
        if ($entityValue === null) {
            return null;
        }

        $handler = $this->factory->getHandlerForValue($entityValue, $property);
        return $handler->normalizeForDatabase($entityValue, $property);
    }

    public function normalizeValuesForDatabase(array $values, object $entity): array
    {
        $reflection = CustomReflection::getInstance($entity)->getClass();
        $normalizedValues = [];

        // Use property cache for better performance
        static $propertyCache = [];
        $className = get_class($entity);

        if (!isset($propertyCache[$className])) {
            $propertyCache[$className] = [];
        }

        foreach ($values as $dbFieldName => $entityPropertyValue) {
            $propertyName = StringUtils::snakeCaseToCamelCase($dbFieldName);

            // Check cache first
            if (!isset($propertyCache[$className][$propertyName])) {
                if ($reflection->hasProperty($propertyName)) {
                    try {
                        $property = $reflection->getProperty($propertyName);
                        $propertyCache[$className][$propertyName] = $property;
                    } catch (ReflectionException $e) {
                        $propertyCache[$className][$propertyName] = null;
                    }
                } else {
                    $propertyCache[$className][$propertyName] = null;
                }
            }

            $property = $propertyCache[$className][$propertyName];

            if ($property !== null) {
                try {
                    $normalizedValues[$dbFieldName] = $this->normalizeFromEntityToDatabase(
                        $entityPropertyValue,
                        $property,
                    );
                } catch (Exception $e) {
                    if ($this->strictMode) {
                        throw $e;
                    }
                    $normalizedValues[$dbFieldName] = $entityPropertyValue;
                }
            } else {
                $normalizedValues[$dbFieldName] = $entityPropertyValue;
            }
        }

        return $normalizedValues;
    }

    public function normalizeBatch(array $data, ?string $context = 'database'): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            switch ($context) {
                case 'database':
                    $normalized[$key] = $this->normalizeQueryValueForDatabase($value);
                    break;
                case 'entity':
                    // Would need entity context
                    break;
                default:
                    $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    public function normalizeValueForDatabase(string $dbFieldName, mixed $rawValue, ?Entity $entity): mixed
    {
        if ($entity) {
            if ($entity->hasProperty($dbFieldName)) {
                try {
                    $property = $entity->getProperty($dbFieldName);
                    return $this->normalizeFromEntityToDatabase($rawValue, $property);
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