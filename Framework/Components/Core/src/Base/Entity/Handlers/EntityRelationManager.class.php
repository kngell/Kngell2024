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
        // Get config if not provided
        if ($relationshipConfig === null) {
            $relationships = $entity->getRelationships();
            $relationshipConfig = $relationships[$dbRelationName] ?? null;

            if (!$relationshipConfig) {
                throw new RuntimeException("Unknown relationship: {$dbRelationName}");
            }
        }

        $entityClass = $relationshipConfig['class'];
        $isCollection = ($relationshipConfig['collection'] ?? false) ||
                       ($relationshipConfig['type'] ?? '') === 'one-to-many';

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
                $this->completeCollectionHydration($entity, $relationKey, $data, $config);
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

            // Set entity data
            foreach ($itemData['_entity_data'] as $key => $value) {
                $nestedEntity->__set($key, $value);
            }

            // Handle nested relationship data
            if (!empty($itemData['_nested'])) {
                foreach ($itemData['_nested'] as $nestedPath => $nestedValue) {
                    $nestedEntity->__set($nestedPath, $nestedValue);
                }
            }

            // Complete hydration recursively
            $nestedEntity->completeHydration();
            $items[] = $nestedEntity;
        }

        // Set collection on parent entity
        $this->setEntityCollection($entity, $propertyName, $items);
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

        if ($value === null) {
            return;
        }

        $primaryKeyDbField = $this->factory->getPrimaryKeyField($entityClass);

        if ($field === $primaryKeyDbField && $value === null) {
            $collection['_skip_row'] = true;
            return;
        }

        if (($collection['_skip_row'] ?? false) || $value === null) {
            return;
        }

        // Handle nested relationship fields (containing dots)
        if (str_contains($field, '.')) {
            if (isset($collection['_current_id'])) {
                $currentId = $collection['_current_id'];
                if (!isset($collection['items'][$currentId])) {
                    $collection['items'][$currentId] = [
                        '_entity_data' => [],
                        '_nested' => [],
                    ];
                }
                $collection['items'][$currentId]['_nested'][$field] = $value;
            } else {
                $collection['_pending_data'][$field] = $value;
            }
            return;
        }

        // Primary key field - start new item
        if ($field === $primaryKeyDbField) {
            $collection['_skip_row'] = false;
            $itemId = (string) $value;

            if (!isset($collection['items'][$itemId])) {
                $collection['items'][$itemId] = [
                    '_entity_data' => [$primaryKeyDbField => $value],
                    '_nested' => [],
                ];
            }

            $collection['_current_id'] = $itemId;
            $collection['items'][$itemId]['_entity_data'][$primaryKeyDbField] = $value;

            // Apply any pending data for this item
            if (isset($collection['_pending_data'])) {
                foreach ($collection['_pending_data'] as $pField => $pValue) {
                    if (str_contains($pField, '.')) {
                        $collection['items'][$itemId]['_nested'][$pField] = $pValue;
                    } else {
                        $collection['items'][$itemId]['_entity_data'][$pField] = $pValue;
                    }
                }
                $collection['_pending_data'] = [];
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
