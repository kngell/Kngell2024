<?php

declare(strict_types=1);

class CartesianHydratorOld
{
    public function __construct(
        private EntityFactoryInterface $entityFactory,
    ) {
    }

    public function hydrateSingle(array $rows, string $entityClass, array $tableAlias, array $tableMap): Entity
    {
        $entity = $this->entityFactory->create(
            $entityClass,
            $tableAlias,
            $tableMap,
        );

        foreach ($rows as $row) {
            foreach ($row as $key => $value) {
                $entity->__set($key, $value);
            }
        }

        $entity->completeHydration();
        $entity->track();
        // dd($entity);
        return $entity;
    }

    public function hydrateCollection(array $rows, string $entityClass, array $tableAlias, array $tableMap): array
    {
        $entities = [];

        if (empty($rows)) {
            return $entities;
        }

        // Get primary key field
        $primaryKeyField = $this->entityFactory->getPrimaryKeyField($entityClass);

        if (!$primaryKeyField) {
            return $this->hydrateSeparateEntities($rows, $entityClass, $tableAlias, $tableMap);
        }

        // Group by primary key
        $prefixedPk = $this->getPrefixedFieldName($primaryKeyField, $rows[0], $tableAlias);
        $groupedRows = $this->groupRowsByPrimaryKey($rows, $prefixedPk);

        // Create one entity per unique ID
        foreach ($groupedRows as $idRows) {
            $entity = $this->entityFactory->create(
                $entityClass,
                $tableAlias,
                $tableMap,
            );

            foreach ($idRows as $row) {
                $entity->prepareRowHydration();
                foreach ($row as $key => $value) {
                    if ($key === 'c_cat_id') {
                        $stop = true;
                    }
                    $entity->__set($key, $value);
                }
            }

            $entity->completeHydration();
            $entity->track();
            $entities[] = $entity;
        }

        return $entities;
    }

    private function hydrateSeparateEntities(array $rows, string $entityClass, array $tableAlias, array $tableMap): array
    {
        $entities = [];

        foreach ($rows as $row) {
            $entity = $this->entityFactory->create(
                $entityClass,
                $tableAlias,
                $tableMap,
            );

            foreach ($row as $key => $value) {
                $entity->__set($key, $value);
            }

            $entity->completeHydration();
            $entity->track();
            $entities[] = $entity;
        }

        return $entities;
    }

    private function groupRowsByPrimaryKey(array $rows, string $primaryKeyField): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $id = $row[$primaryKeyField] ?? null;
            if ($id === null) {
                continue;
            }

            if (!isset($grouped[$id])) {
                $grouped[$id] = [];
            }
            $grouped[$id][] = $row;
        }

        return $grouped;
    }

    private function getPrefixedFieldName(string $fieldName, array $sampleRow, array $tableAlias): string
    {
        foreach ($tableAlias as $alias) {
            $prefixed = $alias . '_' . $fieldName;
            if (isset($sampleRow[$prefixed])) {
                return $prefixed;
            }
        }

        return $fieldName;
    }
}