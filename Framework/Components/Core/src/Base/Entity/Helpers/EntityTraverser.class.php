<?php

declare(strict_types=1);

final readonly class EntityTraverser
{
    public function __construct(
        private TypePresenterFactory $typePresenterFactory,
    ) {
    }

    public function traverse(
        Entity $entity,
        int $maxDepth,
        bool $includeRelationships,
        array $excludedProperties = [],
    ): array {
        return $this->transformEntityToArray(
            $entity,
            null,
            $includeRelationships,
            $maxDepth,
            0,
            $excludedProperties,
        );
    }

    // EntityTraverser.php refactored
    public function transformEntityToArray(
        mixed $value,
        ?ReflectionProperty $property,
        bool $includeRelationships,
        int $maxDepth,
        int $currentDepth,
        array $excludedProperties,
        bool $formatValues = true,
    ): mixed {
        if ($currentDepth >= $maxDepth) {
            return null;
        }

        if ($formatValues && $this->typePresenterFactory !== null && $property !== null) {
            if (!($value instanceof Entity) && !$this->isCollection($value)) {
                $value = $this->typePresenterFactory->displayValue($value, $property);
            }
        }

        if ($this->isCollection($value)) {
            return $this->transformCollection($value, $property, $includeRelationships, $maxDepth, $currentDepth, $excludedProperties, $formatValues);
        }

        if ($value instanceof Entity) {
            return $this->transformSingleEntity($value, $includeRelationships, $maxDepth, $currentDepth, $excludedProperties, $formatValues);
        }

        return $value;
    }

    private function transformSingleEntity(
        Entity $entity,
        bool $includeRelationships,
        int $maxDepth,
        int $currentDepth,
        array $excludedProperties,
    ): array {
        $result = [];
        $reflection = $reflection = CustomReflection::getInstance($entity)->getClass();

        foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $propertyName = $property->getName();

            if (in_array($propertyName, $excludedProperties, true)) {
                continue;
            }

            if ($property->isInitialized($entity)) {
                $value = $property->getValue($entity);
                $dbFieldName = StringUtils::camelCaseToSnakeCase($propertyName); //$entity->getEntityKeyField();

                $isRelationship = $this->isRelationshipProperty($property, $entity);

                if ($isRelationship && !$includeRelationships) {
                    $result[$dbFieldName . '_id'] = $this->extractIdFromEntity($value);
                } elseif ($isRelationship && $includeRelationships) {
                    $result[$dbFieldName] = $this->transformEntityToArray(
                        $value,
                        $property,
                        $includeRelationships,
                        $maxDepth,
                        $currentDepth + 1,
                        $excludedProperties,
                    );
                } else {
                    $result[$dbFieldName] = $this->transformEntityToArray(
                        $value,
                        $property,
                        $includeRelationships,
                        $maxDepth,
                        $currentDepth,
                        $excludedProperties,
                    );
                }
            }
        }

        return $result;
    }

    private function transformCollection(
        mixed $collection,
        ?ReflectionProperty $property,
        bool $includeRelationships,
        int $maxDepth,
        int $currentDepth,
        array $excludedProperties,
    ): array {
        $array = is_array($collection) ? $collection : $collection->toArray();

        return array_map(
            fn ($item) => $this->transformEntityToArray(
                $item,
                $property,
                $includeRelationships,
                $maxDepth,
                $currentDepth,
                $excludedProperties,
            ),
            $array,
        );
    }

    private function isCollection(mixed $value): bool
    {
        return $value instanceof CollectionInterface ||
               (is_array($value) && ArrayUtils::isObjectList($value));
    }

    private function isRelationshipProperty(ReflectionProperty $property, Entity $entity): bool
    {
        $propertyName = $property->getName();
        $relationshipPatterns = $entity->getRelationships();

        foreach ($relationshipPatterns as $pattern) {
            if (stripos($propertyName, $pattern) !== false) {
                return true;
            }
        }

        if ($property->isInitialized($entity)) {
            $value = $property->getValue($entity);
            return $value instanceof Entity ||
                   (is_array($value) && !empty($value) && current($value) instanceof Entity) ||
                   $value instanceof CollectionInterface;
        }

        return false;
    }

    private function extractIdFromEntity(mixed $value): mixed
    {
        if ($value instanceof Entity) {
            return $this->extractId($value);
        }

        if ($value instanceof CollectionInterface || is_array($value)) {
            return array_map(
                fn ($item) => $this->extractId($item),
                $value instanceof CollectionInterface ? $value->all()() : $value,
            );
        }

        return null;
    }

    private function extractId(Entity $entity): mixed
    {
        $keyProperty = $entity->getEntityKeyProperty();
        if ($keyProperty !== false) {
            return $entity->getFieldValue($keyProperty);
        }

        $reflection = $reflection = CustomReflection::getInstance($entity)->getClass();
        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->getName();
            if (str_ends_with(strtolower($propertyName), 'id')) {
                if ($property->isInitialized($entity)) {
                    return $property->getValue($entity);
                }
            }
        }

        return null;
    }
}
