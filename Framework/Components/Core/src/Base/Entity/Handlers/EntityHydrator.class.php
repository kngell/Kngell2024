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
            // dd($data);
            $officialKey = $entity->getRelationshipKeyFromDataKey($key);
            // if ($officialKey === 'footer_menu_link') {
            //     $stop = true;
            // }
            if ($officialKey && is_array($value)) {
                $this->entityFactory->getRelationManager()->hydrateRelatedEntity(
                    $entity,
                    $officialKey,
                    '_all_data',
                    $value,
                    $entity->getTableAlias(),
                    $entity->getTableMap(),
                    $entity->getRelatedEntities(),
                );
            } else {
                if ($key === $entity->getEntityKeyField() && empty($value)) {
                    continue;
                }
                $entity->__set($key, $value);
            }
        }

        $entity->completeHydration();
        return $entity;
    }

    public function getDirtyData(Entity $entity): array
    {
        $changes = $this->changeTracker->getChanges($entity);
        $normalizedData = [];
        $fieldToPropertyMap = $this->mapper->getFieldToPropertyMap($entity);
        $reflection = CustomReflection::getInstance($entity)->getClass();

        foreach ($changes as $dbFieldName => $currentValue) {
            try {
                $propertyName = $fieldToPropertyMap[$dbFieldName] ?? $this->mapper->convertToPropertyName($dbFieldName);
                $property = $reflection->getProperty($propertyName);
                $normalizedData[$dbFieldName] = $this->normalizer->normalizeFromEntityToDatabase(
                    $currentValue,
                    $property,
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
        $reflection = CustomReflection::getInstance($entity)->getClass();

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

            $convertedValue = $this->normalizer->normalizeFromDatabaseToEntity(
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

    private function setPropertyValue(Entity $entity, string $propertyName, mixed $value): void
    {
        $reflection = CustomReflection::getInstance($entity)->getClass();
        try {
            $property = $reflection->getProperty($propertyName);
            if ($value === null && $property->hasType() && !$property->getType()->allowsNull()) {
                return;
            }

            $property->setValue($entity, $value);
        } catch (ReflectionException $e) {
            // Error handling for non-existent property
        }
    }
}