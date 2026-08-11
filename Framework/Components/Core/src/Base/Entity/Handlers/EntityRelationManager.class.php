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
                // $fieldName = substr($name, strlen($alias) + 1);
                $fieldName = substr($name, strlen($prefix));

                // Return as relationship.field for relationships
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

    public function resetCurrentPointers(array &$relatedEntities): void
    {
        foreach ($relatedEntities as $key => &$data) {
            if (isset($data['_is_collection'])) {
                $data['_current_id'] = null;
            }
        }
    }

    public function hasActiveRelationships(
        Entity $entity,
        array $tableAlias,
        array &$relatedEntities,
    ): bool {
        return !empty($relatedEntities) || !empty($tableAlias);
    }

    public function hydrateRelatedEntity(
        Entity $entity,
        string $dbRelationName,
        string $field,
        mixed $value,
        array $tableAlias,
        array $tableMap,
        array &$relatedEntities,
        ?array $relationshipConfig = null,
    ): void {
        if ($relationshipConfig === null) {
            $relationships = $entity->getRelationships();
            $relationshipConfig = $relationships[$dbRelationName] ?? null;

            if (!$relationshipConfig) {
                throw new RuntimeException("Unknown relationship: {$dbRelationName}");
            }
        }

        $primaryKeyField = $entity->getEntityKeyField();
        if ($primaryKeyField && $field === $primaryKeyField && empty($value)) {
            return;
        }

        $entityClass = $relationshipConfig['class'];
        $isCollection = ($relationshipConfig['collection'] ?? false) ||
                       ($relationshipConfig['type'] ?? '') === 'one-to-many';

        // Handle _all_data for collections (cache restoration)
        if ($isCollection && $field === '_all_data' && is_array($value)) {
            $this->hydrateCollectionFromAllData(
                $entity,
                $dbRelationName,
                $value,
                $entityClass,
                $relationshipConfig,
                $tableAlias,
                $tableMap,
            );
            return;
        }

        if ($isCollection) {
            $this->hydrateCollectionItem(
                relationName: $dbRelationName,
                entityClass: $entityClass,
                field: $field,
                value: $value,
                relatedEntities: $relatedEntities,
                parentEntity: $entity,
                relationshipConfig: $relationshipConfig,
            );
            return;
        }

        // Single entity (one-to-one)
        if (!isset($relatedEntities[$dbRelationName])) {
            $relatedEntities[$dbRelationName] = [
                '_entity' => $this->factory->create(
                    $entityClass,
                    $this->extractNestedTableAlias($dbRelationName, $tableAlias),
                    $this->extractNestedTableMap($dbRelationName, $tableAlias, $tableMap),
                ),
                '_config' => $relationshipConfig,
            ];
        }

        if ($field === '_all_data' && is_array($value)) {
            $relatedEntities[$dbRelationName]['_entity']->assign($value);
        } else {
            $relatedEntities[$dbRelationName]['_entity']->__set($field, $value);
        }
    }
    // public function hydrateRelatedEntity(
    //     Entity $entity,
    //     string $dbRelationName,
    //     string $field,
    //     mixed $value,
    //     array $tableAlias,
    //     array $tableMap,
    //     array &$relatedEntities,
    //     ?array $relationshipConfig = null,
    // ): void {
    //     if ($relationshipConfig === null) {
    //         $relationships = $entity->getRelationships();
    //         $relationshipConfig = $relationships[$dbRelationName] ?? null;

    //         if (!$relationshipConfig) {
    //             throw new RuntimeException("Unknown relationship: {$dbRelationName}");
    //         }
    //     }

    //     $primaryKeyField = $entity->getEntityKeyField();
    //     if ($primaryKeyField && $field === $primaryKeyField && empty($value)) {
    //         return;
    //     }

    //     $entityClass = $relationshipConfig['class'];
    //     $isCollection = ($relationshipConfig['collection'] ?? false) ||
    //                    ($relationshipConfig['type'] ?? '') === 'one-to-many';

    //     if ($isCollection) {
    //         $this->hydrateCollectionItem(
    //             relationName: $dbRelationName,
    //             entityClass: $entityClass,
    //             field: $field,
    //             value: $value,
    //             relatedEntities: $relatedEntities,
    //             parentEntity: $entity,
    //             relationshipConfig: $relationshipConfig,
    //         );
    //         return;
    //     }

    //     // Single entity (one-to-one)
    //     if (!isset($relatedEntities[$dbRelationName])) {
    //         $relatedEntities[$dbRelationName] = [
    //             '_entity' => $this->factory->create(
    //                 $entityClass,
    //                 $this->extractNestedTableAlias($dbRelationName, $tableAlias),
    //                 $this->extractNestedTableMap($dbRelationName, $tableAlias, $tableMap),
    //             ),
    //             '_config' => $relationshipConfig,
    //         ];
    //     }

    //     if ($field === '_all_data' && is_array($value)) {
    //         $relatedEntities[$dbRelationName]['_entity']->assign($value);
    //     } else {
    //         $relatedEntities[$dbRelationName]['_entity']->__set($field, $value);
    //     }
    // }

    // public function completeRelatedEntityHydration(Entity $entity, array $relatedEntities): void
    // {
    //     foreach ($relatedEntities as $relationKey => $data) {
    //         // Get relationship config
    //         $relationships = $entity->getRelationships();
    //         $config = $relationships[$relationKey] ?? ($data['_config'] ?? null);

    //         if (!$config) {
    //             continue;
    //         }

    //         $isCollection = ($config['collection'] ?? false) ||
    //                        ($config['type'] ?? '') === 'one-to-many';

    //         if ($isCollection && isset($data['_is_collection'])) {
    //             $this->completeCollectionHydration($entity, $relationKey, $data, $config);
    //         } else {
    //             $this->completeSingleEntityHydration($entity, $relationKey, $data, $config);
    //         }
    //     }
    // }
    public function completeRelatedEntityHydration(Entity $entity, array $relatedEntities): void
    {
        foreach ($relatedEntities as $relationKey => $data) {
            // Get relationship config
            $relationships = $entity->getRelationships();
            $config = $relationships[$relationKey] ?? ($data['_config'] ?? null);

            if (!$config) {
                continue;
            }

            $isCollection = ($config['collection'] ?? false) ||
                           ($config['type'] ?? '') === 'one-to-many';

            if ($isCollection && isset($data['_is_collection'])) {
                // Check if we have pre-hydrated items from cache
                if (isset($data['_hydrated_items'])) {
                    $propertyName = $entity->getRelationPropertyName($relationKey);
                    $this->setEntityCollection($entity, $propertyName, $data['_hydrated_items']);
                } else {
                    $this->completeCollectionHydration($entity, $relationKey, $data, $config);
                }
            } else {
                $this->completeSingleEntityHydration($entity, $relationKey, $data, $config);
            }
        }
    }

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
            $addMethod = 'add' . ucfirst(StringUtils::snakeCaseToCamelCase($relationName));
            if (method_exists($parent, $addMethod)) {
                $parent->$addMethod($childEntity);
            }
        }
    }

    private function hydrateCollectionFromAllData(
        Entity $parentEntity,
        string $relationName,
        array $collectionData,
        string $entityClass,
        array $relationshipConfig,
        array $tableAlias,
        array $tableMap,
    ): void {
        $relatedEntities = &$parentEntity->getRelatedEntities();
        $propertyName = $parentEntity->getRelationPropertyName($relationName);
        $items = [];

        foreach ($collectionData as $itemData) {
            // Create child entity
            $childEntity = $this->factory->create(
                $entityClass,
                $tableAlias,
                $tableMap,
            );

            // Assign data to child entity
            $childEntity->assign($itemData);
            $childEntity->completeHydration();
            $items[] = $childEntity;
        }

        // Store in related entities for completion
        $relatedEntities[$relationName] = [
            '_is_collection' => true,
            '_entity_class' => $entityClass,
            '_config' => $relationshipConfig,
            '_pre_hydrated_items' => $items,  // Store pre-hydrated items
            'items' => [],
        ];

        // Set directly on entity
        $this->setEntityCollection($parentEntity, $propertyName, $items);
    }

    private function completeSingleEntityHydration(Entity $entity, string $relationKey, mixed $data, array $config): void
    {
        $entityClass = $config['class'];
        $propertyName = $entity->getRelationPropertyName($relationKey);

        try {
            if (is_array($data) && isset($data['_entity']) && $data['_entity'] instanceof Entity) {
                $relatedEntity = $data['_entity'];
                $relatedEntity->completeHydration();
            } else {
                $relatedEntity = $this->factory->create(
                    $entityClass,
                    $this->extractNestedTableAlias($relationKey, $entity->getTableAlias()),
                    $this->extractNestedTableMap($relationKey, $entity->getTableAlias(), $entity->getTableMap()),
                );

                if (is_array($data)) {
                    foreach ($data as $key => $value) {
                        $relatedEntity->__set($key, $value);
                    }
                }
                $relatedEntity->completeHydration();
            }

            $property = $entity->getProperty($propertyName);
            if ($property) {
                $property->setValue($entity, $relatedEntity);
            }
        } catch (Exception $e) {
            // Log error but continue
            error_log("Failed to hydrate relationship {$relationKey}: " . $e->getMessage());
        }
    }

    private function setEntityCollection(Entity $entity, string $propertyName, array $items): void
    {
        try {
            $property = $entity->getProperty($propertyName);
            if ($property && $property->getType() && $property->getType()->getName() === 'array') {
                $property->setValue($entity, $items);
                return;
            }

            // Try setter method
            $setMethod = 'set' . ucfirst($propertyName);
            if (method_exists($entity, $setMethod)) {
                $entity->$setMethod($items);
                return;
            }

            // Try adder method
            $singularName = rtrim($propertyName, 's');
            $addMethod = 'add' . ucfirst($singularName);
            if (method_exists($entity, $addMethod)) {
                foreach ($items as $item) {
                    $entity->$addMethod($item);
                }
                return;
            }

            // Fallback: directly set property via reflection
            $reflection = CustomReflection::getInstance($entity)->getClass();
            $property = $reflection->getProperty($propertyName);
            $property->setValue($entity, $items);
        } catch (Exception $e) {
            error_log("Failed to set collection {$propertyName}: " . $e->getMessage());
        }
    }

    private function completeCollectionHydration(Entity $entity, string $relationName, array $collectionData, array $config): void
    {
        $entityClass = $config['class'];
        $propertyName = $entity->getRelationPropertyName($relationName);

        $items = [];
        foreach ($collectionData['items'] as $itemId => $itemData) {
            $nestedEntity = $this->factory->create(
                $entityClass,
                $this->extractNestedTableAlias($relationName, $entity->getTableAlias()),
                $this->extractNestedTableMap($relationName, $entity->getTableAlias(), $entity->getTableMap()),
            );

            $hasData = false;

            // Set entity data
            if (!empty($itemData['_entity_data'])) {
                $hasData = true;
                foreach ($itemData['_entity_data'] as $key => $value) {
                    if ($value !== null && $value !== '') {
                        $nestedEntity->__set($key, $value);
                    }
                }
            }

            // Handle nested collections (one-to-many)
            if (!empty($itemData['_nested_collections'])) {
                foreach ($itemData['_nested_collections'] as $nestedRelation => $nestedCollection) {
                    if (!empty($nestedCollection['_pending'])) {
                        if (!empty($nestedCollection['_pending'])) {
                            $tempItemId = 'pending_' . uniqid();
                            if (!isset($nestedCollection['_grouped'][$tempItemId])) {
                                $nestedCollection['_grouped'][$tempItemId] = [];
                            }
                            foreach ($nestedCollection['_pending'] as $pField => $pValue) {
                                $nestedCollection['_grouped'][$tempItemId][$pField] = $pValue;
                            }
                            unset($nestedCollection['_pending']);
                        }
                    }

                    // Process each grouped nested item
                    if (!empty($nestedCollection['_grouped'])) {
                        $hasData = true;
                        foreach ($nestedCollection['_grouped'] as $nestedItemId => $nestedItemData) {
                            foreach ($nestedItemData as $nestedField => $nestedValue) {
                                $fullPath = $nestedRelation . '.' . $nestedField;
                                $nestedEntity->__set($fullPath, $nestedValue);
                            }
                        }
                    }
                }
            }

            // Handle simple nested data (one-to-one)
            if (!empty($itemData['_nested'])) {
                $hasData = true;
                foreach ($itemData['_nested'] as $nestedPath => $nestedValue) {
                    $nestedEntity->__set($nestedPath, $nestedValue);
                }
            }

            $primaryKey = $nestedEntity->getEntityPrimarykeyValue();
            if ($hasData && $primaryKey !== null && $primaryKey !== 0 && $primaryKey !== '') {
                $nestedEntity->completeHydration();
                $items[] = $nestedEntity;
            } elseif ($hasData && !empty($itemData['_nested_collections'])) {
                $nestedEntity->completeHydration();
                $items[] = $nestedEntity;
            }
        }

        if (!empty($items)) {
            $this->setEntityCollection($entity, $propertyName, $items);
        }
    }

    private function hydrateCollectionItem(
        string $relationName,
        string $entityClass,
        string $field,
        mixed $value,
        array &$relatedEntities,
        Entity $parentEntity,
        array $relationshipConfig,
    ): void {
        if (!isset($relatedEntities[$relationName])) {
            $relatedEntities[$relationName] = [
                '_is_collection' => true,
                '_entity_class' => $entityClass,
                '_config' => $relationshipConfig,
                'items' => [],
                '_current_id' => null,
                '_pending_data' => [],
            ];
        }

        $collection = &$relatedEntities[$relationName];
        $primaryKeyDbField = $this->factory->getPrimaryKeyField($entityClass);

        // Primary key field - start new item
        if ($field === $primaryKeyDbField) {
            $itemId = (string) $value;

            if (!isset($collection['items'][$itemId])) {
                $collection['items'][$itemId] = [
                    '_entity_data' => [],
                    '_nested' => [],
                    '_nested_collections' => [],
                ];
            }

            $collection['_current_id'] = $itemId;
            $collection['items'][$itemId]['_entity_data'][$field] = $value;

            // Apply any pending data for this item
            if (isset($collection['_pending_data'])) {
                foreach ($collection['_pending_data'] as $pField => $pValue) {
                    if (str_contains($pField, '.')) {
                        $this->processPendingNestedField($collection['items'][$itemId], $pField, $pValue, $relationshipConfig);
                    } else {
                        $collection['items'][$itemId]['_entity_data'][$pField] = $pValue;
                    }
                }
                $collection['_pending_data'] = [];
            }
            return;
        }

        // Handle nested relationship fields
        if (str_contains($field, '.')) {
            if (isset($collection['_current_id'])) {
                $currentId = $collection['_current_id'];

                $pathParts = explode('.', $field);
                $nestedRelation = $pathParts[0];
                $nestedField = implode('.', array_slice($pathParts, 1));

                $isNestedCollection = $relationshipConfig['type'] === 'one-to-many';

                if ($isNestedCollection) {
                    $nestedPrimaryKey = $this->factory->getPrimaryKeyField($relationshipConfig['class']);

                    if (!isset($collection['items'][$currentId]['_nested_collections'][$nestedRelation])) {
                        $collection['items'][$currentId]['_nested_collections'][$nestedRelation] = [
                            '_grouped' => [],
                            '_current_id' => null,
                        ];
                    }

                    $nestedCollection = &$collection['items'][$currentId]['_nested_collections'][$nestedRelation];

                    if ($nestedField === $nestedPrimaryKey) {
                        $nestedItemId = (string) $value;
                        if (!isset($nestedCollection['_grouped'][$nestedItemId])) {
                            $nestedCollection['_grouped'][$nestedItemId] = [];
                        }
                        $nestedCollection['_current_id'] = $nestedItemId;
                        $nestedCollection['_grouped'][$nestedItemId][$nestedField] = $value;
                    } elseif ($nestedCollection['_current_id'] !== null) {
                        $currentNestedId = $nestedCollection['_current_id'];
                        $nestedCollection['_grouped'][$currentNestedId][$nestedField] = $value;
                    } else {
                        // Store as pending for this nested collection
                        if (!isset($nestedCollection['_pending'])) {
                            $nestedCollection['_pending'] = [];
                        }
                        $nestedCollection['_pending'][$nestedField] = $value;
                    }
                } else {
                    // One-to-one relationship (variation_type)
                    $collection['items'][$currentId]['_nested'][$field] = $value;
                }
            } else {
                // No current item, store as pending
                $collection['_pending_data'][$field] = $value;
            }
            return;
        }

        // Regular field (not primary key, not nested)
        if (isset($collection['_current_id'])) {
            $currentId = $collection['_current_id'];
            $collection['items'][$currentId]['_entity_data'][$field] = $value;
        } else {
            $collection['_pending_data'][$field] = $value;
        }
    }

    private function processPendingNestedField(array &$item, string $field, mixed $value, array $relationshipConfig): void
    {
        $pathParts = explode('.', $field);
        $nestedRelation = $pathParts[0];
        $nestedField = implode('.', array_slice($pathParts, 1));

        // Use the relationship config to determine if this is a collection
        $isNestedCollection = $relationshipConfig['type'] === 'one-to-many';

        if ($isNestedCollection) {
            $nestedPrimaryKey = $this->factory->getPrimaryKeyField($relationshipConfig['class']);

            if (!isset($item['_nested_collections'][$nestedRelation])) {
                $item['_nested_collections'][$nestedRelation] = [
                    '_grouped' => [],
                    '_current_id' => null,
                    '_pending' => [],
                ];
            }

            $nestedCollection = &$item['_nested_collections'][$nestedRelation];

            if ($nestedField === $nestedPrimaryKey) {
                $nestedItemId = (string) $value;
                if (!isset($nestedCollection['_grouped'][$nestedItemId])) {
                    $nestedCollection['_grouped'][$nestedItemId] = [];
                }
                $nestedCollection['_current_id'] = $nestedItemId;
                $nestedCollection['_grouped'][$nestedItemId][$nestedField] = $value;

                // Apply any pending data for this nested item
                if (isset($nestedCollection['_pending']) && !empty($nestedCollection['_pending'])) {
                    foreach ($nestedCollection['_pending'] as $pField => $pValue) {
                        $nestedCollection['_grouped'][$nestedItemId][$pField] = $pValue;
                    }
                    $nestedCollection['_pending'] = [];
                }
            } elseif ($nestedCollection['_current_id'] !== null) {
                $currentNestedId = $nestedCollection['_current_id'];
                $nestedCollection['_grouped'][$currentNestedId][$nestedField] = $value;
            } else {
                // Store as pending for this nested collection
                $nestedCollection['_pending'][$nestedField] = $value;
            }
        } else {
            // One-to-one relationship
            $item['_nested'][$field] = $value;
        }
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

        return $nested;
    }
}