<?php

declare(strict_types=1);

class QueryResultHydrator
{
    public function __construct(
        private QueryResultConfig $config,
        private EntityFactoryInterface $entityFactory,
        private string $entityClass,
    ) {
    }

    /**
     * Takes raw database data, normalizes it to rich PHP objects, hydrates the entity,
     * and tracks its initial state.
     *
     * @param array $rawDbData The associative array fetched from PDO.
     * @param Entity $entityPrototype The entity instance to clone or a new entity to hydrate.
     *
     * @return Entity The fully hydrated and tracked entity.
     */
    public function hydrateAndTrack(array $rawDbData, Entity $entityPrototype): Entity
    {
        $entity = clone $entityPrototype;

        $reflection = new ReflectionObject($entity);
        $richData = [];

        foreach ($rawDbData as $dbColumnName => $dbValue) {
            // Convert database_field_name to phpPropertyName
            $propertyName = StringUtils::snakeCaseToCamelCase($dbColumnName);

            if (!$reflection->hasProperty($propertyName)) {
                continue;
            }

            $property = $reflection->getProperty($propertyName);

            $normalizedValue = $this->entityFactory->getNormalizer()->normalizeFromDatabaseToEntity(
                rawValue: $dbValue,
                property: $property,
                entityInstance: $entity,
            );
            $richData[$propertyName] = $normalizedValue;
        }

        $entity->pdoHydrate($richData);

        $this->entityFactory->getChangeTracker()->track($entity);

        return $entity;
    }

    public function hydrateWithRelationships(array $rows): Object
    {
        $entity = $this->entityFactory->create($this->entityClass, $this->config->getTableAlias(), $this->config->getTableMap());

        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                $entity->__set($key, $value);
            }
        }
        return $entity;
    }
}