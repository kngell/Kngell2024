<?php

declare(strict_types=1);

class EntityPropertyResolverService
{
    public function __construct(
    ) {
    }

    public function resolve(Entity $entity, mixed $value, string $getterMethod = ''): array
    {
        // Try to guess property name from getter method
        if ($getterMethod !== '') {
            $propertyName = $this->getterToPropertyName($getterMethod);
            $property = $this->getProperty($entity, $propertyName);

            if ($property !== null) {
                return [
                    'property' => $property,
                    'propertyName' => $propertyName,
                    'value' => $value,
                ];
            }
        }

        // Fallback: try to find property by value type
        return $this->resolveByValueType($entity, $value);
    }

    private function getterToPropertyName(string $getterMethod): string
    {
        // Remove 'get' or 'is' prefix
        if (str_starts_with($getterMethod, 'get')) {
            return lcfirst(substr($getterMethod, 3));
        }

        if (str_starts_with($getterMethod, 'is')) {
            return lcfirst(substr($getterMethod, 2));
        }

        return $getterMethod;
    }

    private function getProperty(Entity $entity, string $propertyName): ?ReflectionProperty
    {
        try {
            $reflection = $reflection = CustomReflection::getInstance($entity)->getClass();
            return $reflection->getProperty($propertyName);
        } catch (ReflectionException $e) {
            return null;
        }
    }

    private function resolveByValueType(Entity $entity, mixed $value): array
    {
        // This is more complex - you'd need to analyze entity properties
        // and match by type
        return [
            'property' => null,
            'propertyName' => null,
            'value' => $value,
        ];
    }
}
