<?php

declare(strict_types=1);

class EntityHydrator implements EntityHydratorInterface
{
    public function __construct(
        private TypeNormalizerInterface $normalizer,
        private ChangeTrackerInterface $changeTracker,
        private EntityMapperInterface $mapper,
        private EntityFactoryInterface $entityFactory,
    ) {
    }

    public function pdoHydrate(Entity $entity, array $data): void
    {
        foreach ($data as $key => $value) {
            $entity->__set($key, $value);
        }
        $entity->completeHydration();
        $this->changeTracker->track($entity);
    }

    public function assign(Entity $entity, array $data): Entity
    {
        foreach ($data as $key => $value) {
            // Resolve 'product_variation_show' -> 'product_variation'
            $officialKey = $entity->getRelationshipKeyFromDataKey($key);

            if ($officialKey && is_array($value)) {
                // Use the OFFICIAL key for the buffer
                $this->entityFactory->getRelationManager()->hydrateRelatedEntity(
                    $entity,
                    $officialKey, // Force 'product_variation'
                    '_all_data',
                    $value,
                    $entity->getTableAlias(),
                    $entity->getTableMap(),
                    $entity->getRelatedEntities(),
                );
            } else {
                $entity->__set($key, $value);
            }
        }

        $entity->completeHydration();
        return $entity;
    }

    public function getDirtyData(Entity $entity): array
    {
        // These keys are likely snake_case (e.g., 'short_description')
        $changes = $this->changeTracker->getChanges($entity);
        $normalizedData = [];

        // Get the map to convert 'short_description' -> 'shortDescription'
        $fieldToPropertyMap = $this->mapper->getFieldToPropertyMap($entity);
        $reflection = CustomReflection::getInstance($entity)->getObject();

        foreach ($changes as $dbFieldName => $currentValue) {
            try {
                // 1. Resolve the actual PHP property name
                $propertyName = $fieldToPropertyMap[$dbFieldName] ?? $this->mapper->convertToPropertyName($dbFieldName);

                // 2. Get the property from reflection using the camelCase name
                $property = $reflection->getProperty($propertyName);

                // 3. Keep the DB Field Name as the key for the final array
                $normalizedData[$dbFieldName] = $this->normalizer->normalizeForEntityToDatabase(
                    $currentValue,
                    $property,
                    $entity,
                );
            } catch (ReflectionException $e) {
                // If the property actually doesn't exist, we skip or log
                continue;
            }
        }

        return $normalizedData;
    }

    public function completeMainHydration(Entity $entity, array &$pendingData, ?array &$cachedFieldMap): void
    {
        if (!empty($pendingData)) {
            if ($cachedFieldMap === null) {
                $cachedFieldMap = $this->mapper->getFieldToPropertyMap($entity);
            }

            foreach ($pendingData as $key => $value) {
                $this->denormalizeAndSetProperty($entity, $key, $value);
            }

            $pendingData = [];
        }
    }

    public function denormalizeAndSetProperty(Entity $entity, string $dbFieldName, mixed $rawValue): void
    {
        $fieldToPropertyMap = $this->mapper->getFieldToPropertyMap($entity);
        $reflection = CustomReflection::getInstance($entity)->getObject();

        $propertyName = $fieldToPropertyMap[$dbFieldName] ?? $this->mapper->convertToPropertyName($dbFieldName);

        if (!property_exists($entity, $propertyName)) {
            error_log(sprintf(
                'Field "%s" (mapped to property "%s") does not exist on entity %s. Skipping.',
                $dbFieldName,
                $propertyName,
                $entity::class,
            ));
            return;
        }

        try {
            $property = $reflection->getProperty($propertyName);
            $convertedValue = $this->normalizer->normalizeForDatabaseToEntity(
                rawValue: $rawValue,
                property: $property,
                entityInstance: $entity,
            );

            $this->setPropertyValue($entity, $propertyName, $convertedValue);
        } catch (ReflectionException $e) {
            throw new HydrationException(
                "Failed to hydrate property '{$propertyName}': " . $e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * @return ChangeTrackerInterface
     */
    public function getChangeTracker(): ChangeTrackerInterface
    {
        return $this->changeTracker;
    }

    /**
     * @return TypeNormalizerInterface
     */
    public function getNormalizer(): TypeNormalizerInterface
    {
        return $this->normalizer;
    }

    private function setPropertyValue(Entity $entity, string $propertyName, $value): void
    {
        $reflection = CustomReflection::getInstance($entity)->getObject();
        try {
            $property = $reflection->getProperty($propertyName);
            $property->setValue($entity, $value);
        } catch (ReflectionException $e) {
            // Error handling for non-existent property
        }
    }
}