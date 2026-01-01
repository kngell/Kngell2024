<?php

declare(strict_types=1);

class EntityRelationManager implements EntityRelationManagerInterface
{
    public function __construct(
        private EntityFactoryInterface $factory,
    ) {
    }

    public function resolveRealName(
        Entity $entity,
        string $name,
        array $tableAlias,
        array $tableMap,
        array $relationships,
    ): string {
        if (empty($tableAlias)) {
            return $name;
        }
        foreach ($tableAlias as $logicalKey => $alias) {
            $prefix = $alias . '_';

            if (str_starts_with($name, $prefix)) {
                $physicalTable = $tableMap[$logicalKey] ?? $logicalKey;
                $fieldName = substr($name, strlen($alias) + 1);

                if (str_contains($logicalKey, '.')) {
                    return $logicalKey . '.' . $fieldName;
                }
                if (isset($relationships[$physicalTable])) {
                    return $physicalTable . '.' . $fieldName;
                }
                return $fieldName;
            }
        }
        return $name;
    }

    public function hasActiveRelationships(
        Entity $entity,
        array $tableAlias,
        array &$relatedEntities,
    ): bool {
        if (!empty($relatedEntities)) {
            return true;
        }
        $relationships = $entity->getRelationships();

        foreach ($tableAlias as $relation => $alias) {
            $baseRelation = $this->getBaseTable($relation);
            if (isset($relationships[$baseRelation])) {
                return true;
            }
        }
        return false;
    }

    public function hydrateRelatedEntity(
        Entity $entity,
        string $dbRelationName,
        string $field,
        mixed $value,
        array $tableAlias,
        array $tableMap,
        array &$relatedEntities,
    ): void {
        $entityClass = $propertyName = $entity->getRelationPropertyName($dbRelationName);
        $property = $entity->getProperty($propertyName);
        $isCollection = $property && $property->getType() &&
                       $property->getType()->getName() === 'array';
        $mappingKey = $entity->getRelationshipKeyFromDataKey($dbRelationName);
        if ($isCollection) {
            $this->hydrateCollectionItem($mappingKey, $field, $value, $relatedEntities, $entity);
            return;
        }

        if (!array_key_exists($mappingKey, $relatedEntities)) {
            $relatedEntities[$mappingKey] = $this->factory->create(
                $entityClass,
                $this->extractNestedTableAlias($mappingKey, $tableAlias),
                $this->extractNestedTableMap($mappingKey, $tableAlias, $tableMap),
            );
        }

        if ($field === '_all_data') {
            $relatedEntities[$mappingKey]->assign($value);
        } else {
            $relatedEntities[$mappingKey]->__set($field, $value);
        }
    }

    public function completeRelatedEntityHydration(Entity $entity, array $relatedEntities): void
    {
        foreach ($relatedEntities as $officialKey => $relatedData) {
            // 1. Get Class (e.g., ProductVariationShow)
            $entityClass = $entity->getRelationClassName($officialKey);

            // 2. Get Property (e.g., productVariationShow)
            $propertyName = $entity->getRelationPropertyName($officialKey);

            if (!$entityClass) {
                continue;
            }

            try {
                $property = $entity->getProperty($propertyName);
                // Check if it's a collection (array)
                $isCollection = $property && $property->getType() && $property->getType()->getName() === 'array';

                if ($isCollection && is_array($relatedData) && isset($relatedData['_is_collection'])) {
                    foreach ($relatedData['items'] as $itemData) {
                        // Force entity creation to trigger recursive hydration
                        $relatedEntity = ($itemData instanceof Entity)
                            ? $itemData
                            : $this->factory->createFromClient($entityClass, $itemData);

                        // Use the property name here
                        $this->addToEntityCollection($entity, $propertyName, $relatedEntity);
                    }
                } else {
                    // Single entity logic
                    $relatedEntity = ($relatedData instanceof Entity)
                        ? $relatedData
                        : $this->factory->createFromClient($entityClass, $relatedData);

                    $property->setValue($entity, $relatedEntity);
                }
            } catch (Exception $e) {
                // Property might not exist or be typed differently
            }
        }
    }
    // public function completeRelatedEntityHydration(Entity $entity, array $relatedEntities): void
    // {
    //     foreach ($relatedEntities as $dbRelationName => $relatedData) {
    //         // 1. Get the CLASS name (e.g., ProductVariationShow::class)
    //         $entityClass = $entity->getRelationClassName($dbRelationName);

    //         // 2. Get the PROPERTY name (e.g., 'productVariationShow')
    //         $propertyName = $entity->getRelationPropertyName($dbRelationName);

    //         if (!$entityClass) {
    //             continue;
    //         }

    //         try {
    //             $property = $entity->getProperty($propertyName);
    //             $isCollection = $property && $property->getType() && $property->getType()->getName() === 'array';

    //             if ($isCollection && is_array($relatedData) && isset($relatedData['_is_collection'])) {
    //                 foreach ($relatedData['items'] as $itemData) {
    //                     if (empty($itemData)) {
    //                         continue;
    //                     }

    //                     // Use createFromClient to ensure recursive assign() is called
    //                     $relatedEntity = ($itemData instanceof Entity)
    //                         ? $itemData
    //                         : $this->factory->createFromClient($entityClass, $itemData);

    //                     $this->addToEntityCollection($entity, $dbRelationName, $relatedEntity);
    //                 }
    //             } elseif (!$isCollection && $relatedData instanceof Entity) {
    //                 $relatedData->completeHydration();
    //                 $property->setValue($entity, $relatedData);
    //             }
    //         } catch (Exception $e) {
    //             // Handle missing properties
    //         }
    //     }
    // }

    public function extractPrefixedData(array $data, string $prefix): array
    {
        $result = [];
        $prefixLength = strlen($prefix);

        foreach ($data as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $cleanKey = substr($key, $prefixLength);
                $result[$cleanKey] = $value;
            }
        }

        return $result;
    }

    public function getFactory(): EntityFactoryInterface
    {
        return $this->factory;
    }

    public function setFactory(EntityFactoryInterface $factory): EntityRelationManager
    {
        $this->factory = $factory;

        return $this;
    }

    public function hydrateManyRelated(
        Entity $parent,
        string $relationName,
        array $collectionData,
    ): void {
        $entityClass = $parent->getRelationshipsName($relationName);

        foreach ($collectionData as $data) {
            $childEntity = $this->factory->createFromClient($entityClass, $data);
            $addMethod = 'add' . ucfirst(StringUtils::camelCase($relationName));
            if (method_exists($parent, $addMethod)) {
                $parent->$addMethod($childEntity);
            }
        }
    }

    private function transformDataForChildEntity(array $data, array $childTableAlias): array
    {
        $transformed = [];

        foreach ($data as $key => $value) {
            if (str_contains($key, '.')) {
                $parts = explode('.', $key);

                $current = &$transformed;
                foreach ($parts as $i => $part) {
                    if ($i === count($parts) - 1) {
                        $current[$part] = $value;
                    } else {
                        if (!isset($current[$part])) {
                            $current[$part] = [];
                        }
                        $current = &$current[$part];
                    }
                }
            } else {
                $transformed[$key] = $value;
            }
        }

        return $transformed;
    }

    private function extractNestedTableAlias(string $relationName, array $parentTableAlias): array
    {
        $nested = [];
        $prefix = $relationName . '.';

        foreach ($parentTableAlias as $key => $alias) {
            if (str_starts_with($key, $prefix)) {
                $nestedKey = substr($key, strlen($prefix));
                $nested[$nestedKey] = $alias;
            }
        }

        if (empty($nested)) {
            foreach ($parentTableAlias as $key => $alias) {
                if ($this->isChildOfRelation($key, $relationName)) {
                    $nestedKey = $this->extractNestedKey($key, $relationName);
                    $nested[$nestedKey] = $alias;
                }
            }
        }

        return $nested;
    }

    private function extractNestedTableMap(string $relationName, array $parentTableAlias, array $parentTableMap): array
    {
        $nested = [];
        $prefix = $relationName . '.';

        foreach ($parentTableMap as $key => $physicalTable) {
            if (str_starts_with($key, $prefix)) {
                $nestedKey = substr($key, strlen($prefix));
                $nested[$nestedKey] = $physicalTable;
            }
        }
        if (isset($parentTableMap[$relationName])) {
            $nested[$relationName] = $parentTableMap[$relationName];
        }

        return $nested;
    }

    private function isChildOfRelation(string $key, string $relationName): bool
    {
        $pattern = preg_quote($relationName, '/') . '\\.';
        return (bool) preg_match('/^' . $pattern . '/', $key);
    }

    private function extractNestedKey(string $key, string $relationName): string
    {
        $prefix = $relationName . '.';
        if (str_starts_with($key, $prefix)) {
            return substr($key, strlen($prefix));
        }
        return $key;
    }

    private function hydrateCollectionItem(
        string $relationName,
        string $field,
        mixed $value,
        array &$relatedEntities,
        Entity $parentEntity,
    ): void {
        if (!array_key_exists($relationName, $relatedEntities)) {
            $relatedEntities[$relationName] = [
                '_is_collection' => true,
                'items' => [],
            ];
        }

        $collection = &$relatedEntities[$relationName];

        if (null === $value) {
            return;
        }

        $entityClass = $parentEntity->getRelationshipsName($relationName);
        $primaryKeyField = $this->factory->getPrimaryKeyField($entityClass);

        // --- CASE 1: CACHE/BULK DATA (_all_data is an array of items) ---
        // If we are restoring a collection from cache, $value is usually [[id=>1,...], [id=>2,...]]
        if ($field === '_all_data' && is_array($value) && ArrayUtils::isArrayList($value)) {
            foreach ($value as $index => $itemData) {
                $itemId = (isset($itemData[$primaryKeyField]))
                    ? (string) $itemData[$primaryKeyField]
                    : (string) $index;

                if (!isset($collection['items'][$itemId])) {
                    $collection['items'][$itemId] = [];
                }

                $collection['items'][$itemId] = array_merge($collection['items'][$itemId], $itemData);
            }
            return;
        }

        // --- CASE 2: CARTESIAN / SINGLE ITEM DATA ---
        $itemId = null;

        // Try to determine the Item ID for this specific row
        if ($field === '_all_data' && is_array($value) && isset($value[$primaryKeyField])) {
            $itemId = (string) $value[$primaryKeyField];
        } elseif ($field === $primaryKeyField || $field === $relationName . '.' . $primaryKeyField) {
            $itemId = (string) $value;
        }

        if ($itemId !== null) {
            if (!isset($collection['items'][$itemId])) {
                $collection['items'][$itemId] = [];
            }
            $collection['_current_id'] = $itemId;

            if ($field === '_all_data' && is_array($value)) {
                $collection['items'][$itemId] = array_merge($collection['items'][$itemId], $value);
            } else {
                $collection['items'][$itemId][$field] = $value;
            }

            // Flush any data that arrived before the ID was known
            if (isset($collection['_pending_data'])) {
                foreach ($collection['_pending_data'] as $pField => $pValue) {
                    $collection['items'][$itemId][$pField] = $pValue;
                }
                unset($collection['_pending_data']);
            }
            return;
        }

        // --- CASE 3: PENDING DATA (ID not yet found in this row) ---
        if (isset($collection['_current_id'])) {
            $currentId = $collection['_current_id'];
            if ($field === '_all_data' && is_array($value)) {
                $collection['items'][$currentId] = array_merge($collection['items'][$currentId], $value);
            } else {
                $collection['items'][$currentId][$field] = $value;
            }
        } else {
            if (!isset($collection['_pending_data'])) {
                $collection['_pending_data'] = [];
            }
            $collection['_pending_data'][$field] = $value;
        }
    }
    // private function hydrateCollectionItem(
    //     string $relationName,
    //     string $field,
    //     mixed $value,
    //     array &$relatedEntities,
    //     Entity $parentEntity,
    // ): void {
    //     if (!array_key_exists($relationName, $relatedEntities)) {
    //         $relatedEntities[$relationName] = [
    //             '_is_collection' => true,
    //             'items' => [],
    //         ];
    //     }

    //     $collection = &$relatedEntities[$relationName];

    //     if (null === $value) {
    //         return;
    //     }

    //     $entityClass = $parentEntity->getRelationshipsName($relationName);
    //     $primaryKeyField = $this->factory->getPrimaryKeyField($entityClass);

    //     $itemId = null;

    //     if ($field === '_all_data' && is_array($value) && isset($value[$primaryKeyField])) {
    //         $itemId = (string) $value[$primaryKeyField];
    //     } elseif ($field === $primaryKeyField || $field === $relationName . '.' . $primaryKeyField) {
    //         $itemId = (string) $value;
    //     }

    //     if ($itemId !== null) {
    //         if (!isset($collection['items'][$itemId])) {
    //             $collection['items'][$itemId] = [];
    //         }
    //         $collection['_current_id'] = $itemId;

    //         if ($field === '_all_data' && is_array($value)) {
    //             $collection['items'][$itemId] = array_merge($collection['items'][$itemId], $value);
    //         } else {
    //             $collection['items'][$itemId][$field] = $value;
    //         }

    //         if (isset($collection['_pending_data'])) {
    //             foreach ($collection['_pending_data'] as $pField => $pValue) {
    //                 $collection['items'][$itemId][$pField] = $pValue;
    //             }
    //             unset($collection['_pending_data']);
    //         }
    //         return;
    //     }

    //     if (isset($collection['_current_id'])) {
    //         $currentId = $collection['_current_id'];
    //         if ($field === '_all_data' && is_array($value)) {
    //             $collection['items'][$currentId] = array_merge($collection['items'][$currentId], $value);
    //         } else {
    //             $collection['items'][$currentId][$field] = $value;
    //         }
    //     } else {
    //         if (!isset($collection['_pending_data'])) {
    //             $collection['_pending_data'] = [];
    //         }
    //         $collection['_pending_data'][$field] = $value;
    //     }
    // }

    private function addToEntityCollection(Entity $entity, string $relationName, Entity $item): void
    {
        $getMethod = 'get' . ucfirst(StringUtils::camelCase($relationName));
        if (method_exists($entity, $getMethod)) {
            $currentCollection = $entity->$getMethod() ?? [];

            // Check if item already exists (by ID or object identity)
            $exists = false;
            foreach ($currentCollection as $existingItem) {
                if ($existingItem === $item ||
                     $existingItem->getfieldValue($existingItem->getEntitykeyField()) === $item->getfieldValue($item->getEntitykeyField())) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $currentCollection[] = $item;

                $setMethod = 'set' . ucfirst(StringUtils::camelCase($relationName));
                if (method_exists($entity, $setMethod)) {
                    $entity->$setMethod($currentCollection);
                    return;
                }
            }
        }
        $addMethod = 'add' . ucfirst(StringUtils::camelCase($relationName));

        if (method_exists($entity, $addMethod)) {
            $entity->$addMethod($item);
            return;
        }

        $getMethod = 'get' . ucfirst($relationName);
        $setMethod = 'set' . ucfirst($relationName);

        if (method_exists($entity, $getMethod) && method_exists($entity, $setMethod)) {
            $currentCollection = $entity->$getMethod();
            $currentCollection[] = $item;
            $entity->$setMethod($currentCollection);
            return;
        }

        try {
            $relationKey = $entity->getRelationClassName($relationName);

            if (!$relationKey) {
                return;
            }

            $property = $entity->getProperty($relationKey);
            if ($property->getType()->getName() === 'array') {
                $currentValue = $property->getValue($entity) ?? [];
                $currentValue[] = $item;
                $property->setValue($entity, $currentValue);
            }
        } catch (Exception $e) {
        }
    }

    private function getBaseTable(string $logicalTable): string
    {
        if (preg_match('/^(.+)_join_\d+$/', $logicalTable, $matches)) {
            return $matches[1];
        }

        return $logicalTable;
    }
}