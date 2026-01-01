<?php

declare(strict_types=1);

class InsertDataBuilder
{
    private array $processedData = [];
    private array $columns = [];

    public function __construct(private SqlInsertQuery $query, private array $insertMap)
    {
        $this->processData();
    }

    public function getData(): array
    {
        return $this->processedData;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    private function combineColumnsWithValues(array $columns, array $valuesData): array
    {
        if (ArrayUtils::isMultidimentional($valuesData)) {
            // Batch insert
            return $this->combineColumnsWithBatchValues($columns, $valuesData);
        } else {
            // Single insert - wrap in array for consistency
            return [$this->combineColumnsWithValuesSingle($columns, $valuesData)];
        }
    }

    private function combineColumnsWithValuesSingle(array $columns, array $values): array
    {
        if (count($columns) !== count($values)) {
            throw new InvalidArgumentException(
                sprintf('Columns count (%d) does not match values count (%d)', count($columns), count($values)),
            );
        }

        return array_combine($columns, $values);
    }

    private function combineColumnsWithBatchValues(array $columns, array $batchValues): array
    {
        $result = [];

        foreach ($batchValues as $index => $values) {
            $result[] = $this->combineColumnsWithValuesSingle($columns, $values);
        }

        return $result;
    }
    // public function getData(): array
    // {
    //     list($table, $insertData, $columnsData, $valuesData) = $this->query->getInsertMapFragments($this->insertMap);
    //     if (!$table && !$insertData && !$columnsData && !$valuesData) {
    //         if ($this->query->getEntityManager()->hasData()) {
    //             return [];
    //         } else {
    //             throw new QueryFlowException('The Query is incorrectly defined');
    //         }
    //     }

    //     if ($insertData && $columnsData === null && $valuesData === null) {
    //         if (ArrayUtils::isStringList($insertData)) {
    //             $columnsData = $insertData;
    //         } else {
    //             return $insertData;
    //         }
    //     }
    //     if ($valuesData) {
    //         $valuesData = $this->normalizeValues($valuesData);
    //         if (isset($insertData) && ArrayUtils::isStringList($insertData) && !isset($columnsData)) {
    //             $columnsData = $insertData;
    //         }
    //         if ($columnsData) {
    //             $columnsData = $this->normalizeColumns($columnsData);
    //             return $this->combineColumnsWithValues($columnsData, $valuesData);
    //         }
    //     }
    //     throw new QueryFlowException('The Query is incorrectly defined');
    // }
    private function processData(): void
    {
        list($table, $insertData, $columnsData, $valuesData) = $this->query->getInsertMapFragments($this->insertMap);

        // Case 1: Entity data
        if (!$table && !$insertData && !$columnsData && !$valuesData) {
            if ($this->query->getEntityManager()->hasData()) {
                $this->processEntityData();
                return;
            }
            throw new QueryFlowException('The Query is incorrectly defined - no data provided');
        }

        // Case 2: insert(data) - associative array or key/value pairs
        if ($insertData && $columnsData === null && $valuesData === null) {
            $this->processInsertData($insertData);
            return;
        }

        // Case 3: columns()->values()
        if ($columnsData && $valuesData) {
            $this->processColumnsAndValues($columnsData, $valuesData);
            return;
        }

        // Case 4: Only columns specified (waiting for values)
        if ($columnsData && !$valuesData) {
            $this->columns = $this->normalizeColumns($columnsData);
            return;
        }

        throw new QueryFlowException('The Query is incorrectly defined - unable to process data');
    }

    private function processInsertData(array $insertData): void
    {
        if (ArrayUtils::isStringList($insertData)) {
            // insert() was called with columns only
            $this->columns = $insertData;
            // Values will be provided later via values() method
        } else {
            // insert() was called with data
            if (count($insertData) === 1 && isset($insertData[0])) {
                $insertData = $insertData[0];
                $this->columns = $insertData instanceof Entity ? array_keys($insertData->toArray()) : array_keys($insertData);
            } elseif (ArrayUtils::isAssoc($insertData)) {
                $this->columns = array_keys($insertData);
            }
            $this->processedData = $insertData;
        }
    }

    private function processColumnsAndValues(array $columnsData, array $valuesData): void
    {
        $this->columns = $this->normalizeColumns($columnsData);
        $valuesData = $this->normalizeValues($valuesData);
        $this->processedData = $this->combineColumnsWithValues($this->columns, $valuesData);
    }

    private function processEntityData(): void
    {
        $em = $this->query->getEntityManager();
        $entity = $em->getEntity();

        if ($entity instanceof Entity) {
            $data = $em->getEntityProperties();
            $this->processedData = $data;
            $this->columns = array_keys($data);
        } elseif ($entity instanceof CollectionInterface) {
            $batchData = [];
            foreach ($entity as $singleEntity) {
                $batchData[] = $singleEntity->toArray();
            }
            $this->processedData = $batchData;
            $this->columns = array_keys($batchData[0] ?? []);
        }
    }

    private function normalizeValues(array $valuesData): array
    {
        $isBatchInsert = ArrayUtils::isMultidimentional($valuesData) &&
                    ArrayUtils::isSequential($valuesData) &&
                    is_array(ArrayUtils::first($valuesData));

        if ($isBatchInsert) {
            $firstRowCount = count($valuesData);
            foreach ($valuesData as $index => $row) {
                if (!is_array($row) || count($row) !== $firstRowCount) {
                    throw new InvalidArgumentException(
                        "Batch insert row $index has inconsistent number of values",
                    );
                }
            }
        }
        return $valuesData;
    }

    private function normalizeColumns(array $columns): array
    {
        if (ArrayUtils::isStringList($columns)) {
            return $columns;
        }
        if (count($columns) === 1 && isset($columns[0])) {
            $this->normalizeColumns($columns[0]);
        }
        throw new Exception('Columns ' . implode(', ', $columns) . 'does not match required format. Please provide a list of columns');
    }
}