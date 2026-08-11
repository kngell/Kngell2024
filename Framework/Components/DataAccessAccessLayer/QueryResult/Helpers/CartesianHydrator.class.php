<?php

declare(strict_types=1);

class CartesianHydrator
{
    public function __construct(
        private EntityFactoryInterface $entityFactory,
    ) {
    }

    public function hydrateSingle(array $rows, string $entityClass, array $tableAlias, array $tableMap): Entity
    {
        if (empty($rows)) {
            return $this->entityFactory->create($entityClass, $tableAlias, $tableMap);
        }

        $primaryKeyField = $this->entityFactory->getPrimaryKeyField($entityClass);
        $prefixedPk = $this->getPrefixedFieldName($primaryKeyField, $rows[0], $tableAlias);
        $rootPrefix = $this->getPrefixFromKey($prefixedPk);

        $groupedRows = $this->groupRowsByPrimaryKey($rows, $prefixedPk);
        $idRows = reset($groupedRows);

        $entity = $this->entityFactory->create($entityClass, $tableAlias, $tableMap);
        foreach ($idRows as $index => $row) {
            if ($index > 0) {
                $entity->prepareRowHydration();
            }

            $segmentedRow = $this->segmentRowByPrefix($row, $rootPrefix);
            $rowKeys = array_keys($row);

            foreach ($segmentedRow as $prefix => $packet) {
                if (!$packet['is_root']) {
                    $pkKey = $this->findPrefixedPrimaryKey($rowKeys, $prefix);
                    if ($pkKey !== null && ($row[$pkKey] ?? null) === null) {
                        continue;
                    }
                }
                foreach ($packet['data'] as $key => $value) {
                    $entity->__set($key, $value);
                }
            }
        }

        $entity->completeHydration();
        $entity->track();
        return $entity;
    }

    public function hydrateCollection(array $rows, string $entityClass, array $tableAlias, array $tableMap): array
    {
        $entities = [];

        if (empty($rows)) {
            return $entities;
        }

        $primaryKeyField = $this->entityFactory->getPrimaryKeyField($entityClass);

        if (!$primaryKeyField) {
            return $this->hydrateSeparateEntities($rows, $entityClass, $tableAlias, $tableMap);
        }

        $prefixedPk = $this->getPrefixedFieldName($primaryKeyField, $rows[0], $tableAlias);
        $rootPrefix = $this->getPrefixFromKey($prefixedPk);
        $groupedRows = $this->groupRowsByPrimaryKey($rows, $prefixedPk);

        foreach ($groupedRows as $idRows) {
            $entity = $this->entityFactory->create($entityClass, $tableAlias, $tableMap);

            foreach ($idRows as $index => $row) {
                if ($index > 0) {
                    $entity->prepareRowHydration();
                }
                $segmentedRow = $this->segmentRowByPrefix($row, $rootPrefix);
                $rowKeys = array_keys($row);
                foreach ($segmentedRow as $prefix => $packet) {
                    if (!$packet['is_root']) {
                        $pkKey = $this->findPrefixedPrimaryKey($rowKeys, $prefix);
                        if ($pkKey !== null && ($row[$pkKey] ?? null) === null) {
                            continue;
                        }
                    }

                    foreach ($packet['data'] as $key => $value) {
                        $entity->__set($key, $value);
                    }
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
            $entity = $this->entityFactory->create($entityClass, $tableAlias, $tableMap);
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

    private function getPrefixFromKey(string $key): string
    {
        return explode('_', $key, 2)[0];
    }

    private function findPrefixedPrimaryKey(array $rowKeys, string $prefix): ?string
    {
        foreach ($rowKeys as $key) {
            if (str_starts_with($key, $prefix . '_') && str_ends_with($key, '_id') && !str_ends_with($key, 'public_id')) {
                return $key;
            }
        }
        return null;
    }

    private function segmentRowByPrefix(array $row, string $rootPrefix): array
    {
        $segmented = [];

        foreach ($row as $key => $value) {
            $prefix = $this->getPrefixFromKey($key);

            if (!isset($segmented[$prefix])) {
                $segmented[$prefix] = [
                    'is_root' => ($prefix === $rootPrefix),
                    'data' => [],
                ];
            }
            $segmented[$prefix]['data'][$key] = $value;
        }

        return $segmented;
    }
}