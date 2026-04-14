<?php

declare(strict_types=1);

readonly class EntityToArrayTransformerOLD implements EntityToArrayTransformerInterface
{
    public function __construct(
        private TypeHandlerFactory $typeHandlerFactory,
        private TypePresenterFactory $typePresenterFactory,
    ) {
    }

    public function toArray(Entity $entity): array
    {
        $array = [];
        $reflection = $reflection = CustomReflection::getInstance($entity)->getClass();

        foreach ($reflection->getProperties(ReflectionProperty::IS_PRIVATE) as $property) {
            $propertyName = $property->getName();

            if ($property->isInitialized($entity)) {
                $dbFieldName = StringUtils::camelCaseToSnakeCase($propertyName);
                $array[$dbFieldName] = $property->getValue($entity);
            }
        }

        return $array;
    }

    public function toOriginalArray(Entity $entity): array
    {
        $array = [];
        $reflection = $reflection = CustomReflection::getInstance($entity)->getClass();

        foreach ($reflection->getProperties() as $prop) {
            $name = StringUtils::studlyCapsToUnderscore($prop->getName());
            $array[$name] = $prop->getValue($entity);
        }

        return $array;
    }

    public function toDeepArray(
        Entity $entity,
        bool $includeRelationships = true,
        int $maxDepth = 2,
        array $excludedProperties = [],
    ): array {
        return $this->transformEntityToArray(
            $entity,
            null,
            $includeRelationships,
            $maxDepth,
            0,
            $excludedProperties,
            $this->typePresenterFactory,
        );
    }

    public function toFlattenedArray(
        Entity $entity,
        string $separator = '.',
        bool $includeRelationships = true,
        array $excludedProperties = [],
    ): array {
        $deepArray = $this->toDeepArray($entity, $includeRelationships, 2, $excludedProperties);
        return $this->flattenArray($deepArray, $separator);
    }

    public function toFormArray(
        Entity $entity,
        array $fieldMapping = [],
        bool $flattenNested = true,
        bool $formatValues = true,
    ): array {
        if (empty($fieldMapping)) {
            $deepArray = $this->toDeepArray($entity, true, 3);
            return $this->flattenArray($deepArray, '.');
        }

        $mappedData = $this->applyFieldMapping($entity, $fieldMapping);

        // We use '.' as separator to ensure 'variations.0.name' becomes a flat key if needed
        return $flattenNested ? $this->flattenArray($mappedData, '.') : $mappedData;
    }

    // public function toFormArray(
    //     Entity $entity,
    //     array $fieldMapping = [],
    //     bool $flattenNested = true,
    //     bool $formatValues = true,
    // ): array {
    //     $deepArray = $this->toDeepArray($entity, true, 3);

    //     if (!empty($fieldMapping)) {
    //         $mappedData = $this->applyFieldMapping($deepArray, $fieldMapping);
    //         return $this->flattenArray($mappedData, '.');
    //     }

    //     return $this->flattenArray($deepArray, '.');
    // }

    public function toDatabaseArray(
        Entity $entity,
        bool $includeRelationships = false,
    ): array {
        $array = $this->toArray($entity);

        foreach ($array as $key => &$value) {
            $value = $this->formatForDatabase($value, $key);
        }

        if ($includeRelationships) {
            foreach ($entity->getRelationships() as $relationName => $relationClass) {
                try {
                    $getter = 'get' . ucfirst($relationName);
                    if (method_exists($entity, $getter)) {
                        $relation = $entity->$getter();
                        if ($relation instanceof Entity) {
                            $array[$relationName . '_id'] = $this->extractId($relation);
                        }
                    }
                } catch (Exception $e) {
                    // Skip if relationship can't be loaded
                }
            }
        }

        return $array;
    }

    public function extractRelationshipIds(
        Entity $entity,
        array $relationshipNames = [],
    ): array {
        $ids = [];

        $relationships = empty($relationshipNames)
            ? array_keys($entity->getRelationships())
            : $relationshipNames;

        foreach ($relationships as $relationName) {
            try {
                $getter = 'get' . ucfirst($relationName);
                if (method_exists($entity, $getter)) {
                    /** @var Entity */
                    $relation = $entity->$getter();
                    if ($relation instanceof Entity) {
                        $ids[$relationName . '_id'] = $this->extractId($relation);
                    } elseif ($relation instanceof CollectionInterface || is_array($relation)) {
                        $ids[$relationName . '_ids'] = array_map(
                            fn ($item) => $this->extractId($item),
                            is_array($relation) ? $relation : $relation->toArray(),
                        );
                    }
                }
            } catch (Exception $e) {
                // Skip if relationship can't be loaded
            }
        }

        return $ids;
    }

    // --------------------------------------------------------------------
    // PRIVATE METHODS
    // --------------------------------------------------------------------

    private function transformEntityToArray(
        mixed $value,
        ?ReflectionProperty $property,
        bool $includeRelationships,
        int $maxDepth,
        int $currentDepth,
        array $excludedProperties,
        ?TypePresenterFactory $presenterFactory = null,
    ): mixed {
        if ($currentDepth >= $maxDepth) {
            return null;
        }
        if ($presenterFactory !== null && !($value instanceof Entity) && !$this->isCollection($value)) {
            $value = $presenterFactory->displayValue($value, $property);
        }
        if ($value instanceof CollectionInterface ||
            (is_array($value) && (ArrayUtils::isObjectList($value)))) {
            $items = $value instanceof CollectionInterface ? $value->all() : $value;
            $transformed = [];

            foreach ($items as $item) {
                $transformed[] = $this->transformEntityToArray(
                    $item,
                    $property,
                    $includeRelationships,
                    $maxDepth,
                    $currentDepth,
                    $excludedProperties,
                    $presenterFactory,
                );
            }

            if ($presenterFactory !== null && !empty($transformed)) {
                $firstItem = reset($transformed);
                if (!is_array($firstItem) && !is_object($firstItem)) {
                    return $presenterFactory->displayValue($transformed, $property);
                }
            }

            return $transformed;
        }

        if ($presenterFactory !== null) {
            $value = $presenterFactory->displayValue($value, $property);
        }

        if ($value instanceof Entity) {
            return $this->transformSingleEntity(
                $value,
                $includeRelationships,
                $maxDepth,
                $currentDepth,
                $excludedProperties,
                $presenterFactory,
            );
        }

        if (is_array($value) || $value instanceof CollectionInterface) {
            return $this->transformCollection(
                $value,
                $property,
                $includeRelationships,
                $maxDepth,
                $currentDepth,
                $excludedProperties,
                $presenterFactory,
            );
        }

        return $value;
    }

    private function transformSingleEntity(
        Entity $entity,
        bool $includeRelationships,
        int $maxDepth,
        int $currentDepth,
        array $excludedProperties,
        ?TypePresenterFactory $presenterFactory,
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
                $dbFieldName = StringUtils::camelCaseToSnakeCase($propertyName);

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
                        $presenterFactory,
                    );
                } else {
                    $result[$dbFieldName] = $this->transformEntityToArray(
                        $value,
                        $property,
                        $includeRelationships,
                        $maxDepth,
                        $currentDepth,
                        $excludedProperties,
                        $presenterFactory,
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
        ?TypePresenterFactory $presenterFactory,
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
                $presenterFactory,
            ),
            $array,
        );
    }

    private function formatForDatabase(mixed $value, string $fieldName): mixed
    {
        try {
            $handler = $this->typeHandlerFactory->getHandlerForQueryValue($value, $fieldName);

            if (method_exists($handler, 'normalizeForDatabase')) {
                return $handler->normalizeForDatabase($value);
            }
            return $value;
        } catch (Throwable $e) {
            error_log("Type handling failed for field {$fieldName}: " . $e->getMessage());
            return $value;
        }
    }

    private function flattenArray(array $array, string $separator = '.', string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix . (empty($prefix) ? '' : $separator) . $key;
            $isFormArray = str_ends_with((string) $key, '[]');

            if (is_array($value) && !$isFormArray && !empty($value)) {
                $result = array_merge($result, $this->flattenArray($value, $separator, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }

    private function applyFieldMapping(mixed $source, array $mapping): array
    {
        $mappedData = [];

        foreach ($mapping as $sourcePath => $targetPath) {
            if (str_contains($sourcePath, '.*.')) {
                // Handle collections (Variations, Gallery, etc.)
                $this->handleWildcardMapping($source, $mappedData, $sourcePath, $targetPath);
            } else {
                // Handle direct fields (Name, SKU, Category ID)
                $value = $this->getNestedValue($source, $sourcePath);
                $value = $this->applyFieldConstraints($targetPath, $value);
                $this->setNestedValue($mappedData, $targetPath, $value);
            }
        }

        return $mappedData;
    }
    // private function applyFieldMapping(array|Entity $data, array $fieldMapping): array
    // {
    //     $mappedData = [];

    //     foreach ($fieldMapping as $source => $target) {
    //         if (str_contains($source, '.*.')) {
    //             $this->handleWildcardMapping($data, $mappedData, $source, $target);
    //             continue;
    //         }

    //         $value = str_contains($source, '.')
    //             ? $this->getNestedValue($data, $source)
    //             : ($data[$source] ?? null);
    //         $value = $this->applyFieldConstraints($target, $value);
    //         $this->setNestedValue($mappedData, $target, $value);
    //     }

    //     return $mappedData;
    // }

    private function handleWildcardMapping(mixed $data, array &$mappedData, string $source, string $target): void
    {
        [$sourceBase, $sourceLeaf] = explode('.*.', $source);
        $collection = $this->getNestedValue($data, $sourceBase);

        if (!is_array($collection) && !($collection instanceof Traversable)) {
            $this->setNestedValue($mappedData, $target, []);
            return;
        }

        $results = [];
        foreach ($collection as $index => $item) {
            // --- FIX IS HERE ---
            // Use getNestedValue instead of array access $item[$sourceLeaf]
            $val = $this->getNestedValue($item, $sourceLeaf);
            // -------------------

            if (str_ends_with($target, '[]')) {
                if ($val !== null) {
                    $results[] = $this->applyFieldConstraints($target, $val);
                }
            } else {
                $specificTargetPath = str_replace(['*', '{i}'], (string) $index, $target);
                $cleanVal = $this->applyFieldConstraints($specificTargetPath, $val);
                $this->setNestedValue($mappedData, $specificTargetPath, $cleanVal);
            }
        }

        if (str_ends_with($target, '[]')) {
            $this->setNestedValue($mappedData, $target, $results);
        }
    }

    private function applyFieldConstraints(string $target, mixed $value): mixed
    {
        if (str_contains($target, 'price') || str_contains($target, 'modifier')) {
            return $this->stripFormatting($value);
        }

        if ($target === 'main_image[]') {
            return is_array($value) ? array_slice($value, 0, 2) : ($value ? [$value] : []);
        }

        return $value;
    }

    private function stripFormatting(mixed $value): mixed
    {
        if (!is_string($value) || empty($value)) {
            return $value;
        }

        // Remove everything except digits, dots, and commas
        $clean = preg_replace('/[^\d,.]/', '', $value);

        // If both . and , exist, it's a formatted number (e.g., 1.234,56)
        if (str_contains($clean, '.') && str_contains($clean, ',')) {
            $dotPos = strrpos($clean, '.');
            $commaPos = strrpos($clean, ',');

            if ($dotPos > $commaPos) {
                // US style: 1,234.56 -> remove comma
                $clean = str_replace(',', '', $clean);
            } else {
                // EU style: 1.234,56 -> remove dot, change comma to dot
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            }
        } elseif (str_contains($clean, ',')) {
            // Only a comma exists: 154,00 -> 154.00
            $clean = str_replace(',', '.', $clean);
        }

        return $clean;
    }

    // private function getNestedValue(array $array, string $path, mixed $default = null): mixed
    // {
    //     $keys = explode('.', $path);
    //     $value = $array;

    //     foreach ($keys as $key) {
    //         // $key = StringUtils::snakeCaseToCamelCase($key);
    //         if (!is_array($value) || !array_key_exists($key, $value)) {
    //             return $default;
    //         }
    //         $value = $value[$key];
    //     }

    //     return $value;
    // }
    private function getNestedValue(mixed $current, string $path, mixed $default = null): mixed
    {
        if ($current === null) {
            return $default;
        }

        $keys = explode('.', $path);
        $lastProperty = null;

        foreach ($keys as $key) {
            if ($current instanceof Entity) {
                $camelKey = StringUtils::snakeCaseToCamelCase($key);
                $lastProperty = $current->getProperty($key);
                $getter = 'get' . $camelKey;

                if (method_exists($current, $getter)) {
                    $current = $current->$getter();
                } elseif (method_exists($current, 'getFieldValue')) {
                    $current = $current->getFieldValue($key);
                } else {
                    return $default;
                }
            } elseif (is_array($current)) {
                $lastProperty = null;
                if (!array_key_exists($key, $current)) {
                    return $default;
                }
                $current = $current[$key];
            } else {
                return $default;
            }

            if ($current === null) {
                return $default;
            }
        }
        if ($this->isCollection($current)) {
            return $current;
        }

        return $this->presentValue($current, $lastProperty);
    }

    /**
     * Helper to check if we are dealing with a list of objects.
     */
    private function isCollection(mixed $value): bool
    {
        return $value instanceof CollectionInterface ||
               (is_array($value) && ArrayUtils::isObjectList($value));
    }

    private function presentValue(mixed $value, ?ReflectionProperty $property): mixed
    {
        if ($value === null) {
            return null;
        }

        // Use the Factory with the proper context
        return $this->typePresenterFactory->displayValue($value, $property);
    }

    private function setNestedValue(array &$array, string $path, mixed $value): void
    {
        $keys = explode('.', $path);
        $current = &$array;

        foreach ($keys as $key) {
            if (!isset($current[$key]) || !is_array($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        $current = $value;
    }

    private function isRelationshipProperty(ReflectionProperty $property, Entity $entity): bool
    {
        $propertyName = $property->getName();

        // Check against known relationship patterns
        $relationshipPatterns = $entity->getRelationships();

        foreach ($relationshipPatterns as $pattern) {
            if (stripos($propertyName, $pattern) !== false) {
                return true;
            }
        }

        // Check if value is an Entity
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
            return array_map(fn ($item) => $this->extractId($item), $value instanceof Entity ? $value->toArray() : $value);
        }

        return null;
    }

    private function extractId(Entity $entity): mixed
    {
        $keyProperty = $entity->getEntityKeyProperty();
        if ($keyProperty !== false) {
            $getter = 'get' . ucfirst($keyProperty);
            if (method_exists($entity, $getter)) {
                return $entity->$getter();
            }

            if (property_exists($entity, $keyProperty)) {
                try {
                    return $entity->$keyProperty;
                } catch (Error $e) {
                    // Property is private, need reflection
                }
            }
            $reflection = $reflection = CustomReflection::getInstance($entity)->getClass();
            if ($reflection->hasProperty($keyProperty)) {
                $property = $reflection->getProperty($keyProperty);
                if ($property->isInitialized($entity)) {
                    return $property->getValue($entity);
                }
            }
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
