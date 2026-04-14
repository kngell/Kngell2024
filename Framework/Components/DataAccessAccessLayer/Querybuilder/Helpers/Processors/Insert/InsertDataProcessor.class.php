<?php

declare(strict_types=1);

class InsertDataProcessor
{
    public function __construct(
        private SqlInsertQuery $query,
        private array $insertMap,
    ) {
    }

    public function process(): ProcessedInsertData
    {
        list($table, $insertData, $columnsData, $valuesData) = $this->query->getMapFragments($this->insertMap, ['insert', 'columns', 'values'], 'into');

        $data = $this->resolveData($insertData, $columnsData, $valuesData);
        $explicitColumns = $this->resolveExplicitColumns($columnsData, $insertData);
        $sourceType = $this->determineSourceType($insertData, $columnsData, $valuesData);

        return new ProcessedInsertData($data, $explicitColumns, $sourceType);
    }

    private function resolveData(?array $insertData, ?array $columnsData, ?array $valuesData): array
    {
        // Case 1: Entity data from EntityManager (no method calls)
        if ($insertData === null && $columnsData === null && $valuesData === null) {
            return $this->getEntityData();
        }

        // Case 2: insert() method called with data (already standardized)
        if ($insertData !== null && $columnsData === null && $valuesData === null) {
            return $this->processInsertData($insertData);
        }

        // Case 3: columns() + values() methods (both already standardized)
        if ($columnsData !== null && $valuesData !== null) {
            return $this->combineColumnsAndValues($columnsData, $valuesData);
        }

        // Case 4: Only columns specified (values will come later)
        if ($columnsData !== null && $valuesData === null) {
            return []; // Empty data for now
        }

        // Case 5: Only values specified (should have columns from insert() or entity)
        if ($valuesData !== null && $columnsData === null) {
            return $this->processValuesOnly($valuesData, $insertData);
        }

        throw new QueryFlowException('Invalid INSERT method combination');
    }

    private function processValuesOnly(array $valuesData, ?array $insertData): array
    {
        // Check if insert() was already called with data (duplicate data error)
        if ($insertData !== null && $this->isAssociativeData($insertData)) {
            throw new QueryFlowException(
                'Cannot use values() after insert() with data. ' .
                'insert() already provided data: ' . implode(', ', array_keys($insertData)),
            );
        }

        // If insert() was called with columns, combine with values
        if ($insertData !== null && ArrayUtils::isStringList($insertData)) {
            return $this->combineColumnsAndValues($insertData, $valuesData);
        }

        // Handle values data (already standardized by InsertDataStandardizer)
        return $this->processValuesData($valuesData);
    }

    private function processValuesData(array $valuesData): array
    {
        // Values data is already standardized, so we just need to handle the structure

        // Single associative array
        if ($this->isAssociativeData($valuesData)) {
            return [$valuesData];
        }

        // Simple value list - we need columns from somewhere else
        // This will be validated later in ProcessedInsertData
        if (ArrayUtils::isSequential($valuesData) && !ArrayUtils::isStringList($valuesData)) {
            return [$valuesData];
        }

        // If we get here, it's an unexpected format after standardization
        throw new InvalidArgumentException(
            'values() method received unsupported data format after standardization.',
        );
    }

    private function processInsertData(array $insertData): array
    {
        // Data is already standardized by InsertDataStandardizer

        // If it's a string list, it's columns-only (no data yet)
        if (ArrayUtils::isStringList($insertData)) {
            return [];
        }
        // if a single or collection or array of entities
        if (ArrayUtils::isObjectList($insertData)) {
            return $insertData;
        }

        // Handle standardized insert data
        return $this->processStandardizedInsertData($insertData);
    }

    private function processStandardizedInsertData(array $insertData): array
    {
        // Single associative array (already standardized from key/value pairs or associative)
        if ($this->isAssociativeData($insertData)) {
            return [$insertData];
        }

        // If we get here, it's an unexpected format after standardization
        throw new InvalidArgumentException(
            'insert() method received unsupported data format after standardization.',
        );
    }

    private function isAssociativeData(array $data): bool
    {
        return ArrayUtils::isAssoc($data) && !ArrayUtils::isStringList($data);
    }

    private function combineColumnsAndValues(array $columnsData, array $valuesData): array
    {
        $columns = ArrayUtils::flattenArrayRecursive($columnsData);

        // Values data is already standardized, so we just need to combine

        if (ArrayUtils::isMultidimentional($valuesData)) {
            // Batch insert with standardized values
            $result = [];
            foreach ($valuesData as $values) {
                if (count($columns) !== count($values)) {
                    throw new InvalidArgumentException(
                        sprintf('Column count (%d) does not match values count (%d)', count($columns), count($values)),
                    );
                }
                $result[] = array_combine($columns, $values);
            }
            return $result;
        } else {
            // Single insert with standardized values
            if (count($columns) !== count($valuesData)) {
                throw new InvalidArgumentException(
                    sprintf('Column count (%d) does not match values count (%d)', count($columns), count($valuesData)),
                );
            }
            return [array_combine($columns, $valuesData)];
        }
    }

    private function resolveExplicitColumns(?array $columnsData, ?array $insertData): ?array
    {
        // Explicit columns from columns() method (already standardized)
        if ($columnsData !== null) {
            return ArrayUtils::flattenArrayRecursive($columnsData);
        }

        // Columns from insert() string list (already standardized)
        if ($insertData !== null && ArrayUtils::isStringList($insertData)) {
            return $insertData;
        }

        return null;
    }

    private function determineSourceType(?array $insertData, ?array $columnsData, ?array $valuesData): string
    {
        if ($insertData === null && $columnsData === null && $valuesData === null) {
            return 'entity_manager';
        }
        if ($insertData !== null && ArrayUtils::isStringList($insertData)) {
            return 'columns_only';
        }
        if ($columnsData !== null && $valuesData !== null) {
            return 'columns_and_values';
        }
        if ($insertData !== null) {
            return 'insert_data';
        }
        if ($valuesData !== null) {
            return 'values_only';
        }
        return 'unknown';
    }

    private function getEntityData(): array
    {
        $em = $this->query->getEntityManager();

        if (!$em->hasData()) {
            return [];
        }

        $entity = $em->getEntity();

        if ($entity instanceof Entity) {
            return [$em->getEntityProperties()];
        }
        if ($entity instanceof CollectionInterface) {
            $batchData = [];
            foreach ($entity as $singleEntity) {
                $batchData[] = $singleEntity->toArray();
            }
            return $batchData;
        }

        return [];
    }
}
