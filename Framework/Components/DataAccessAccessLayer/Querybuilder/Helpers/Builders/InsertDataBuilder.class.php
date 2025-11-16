<?php

declare(strict_types=1);

class InsertDataBuilder
{
    public function __construct(private SqlInsertQuery $query, private array $insertMap)
    {
    }

    public function getData(): array
    {
        list($table, $insertData, $columnsData, $valuesData) = $this->query->getInsertMapFragments($this->insertMap);

        if ($insertData && $columnsData === null && $valuesData === null) {
            if (ArrayUtils::isStringList($insertData)) {
                $columnsData = $insertData;
            } else {
                return $insertData;
            }
        }
        if ($valuesData) {
            $valuesData = $this->normalizeValues($valuesData);
            if ($columnsData) {
                $columnsData = $this->normalizeColumns($columnsData);
                return $this->combineColumnsWithValues($columnsData, $valuesData);
            }
        }
        throw new QueryFlowException('The Query is incorrectly defined');
    }

    public function combineColumnsWithValues(array $columns, array $valuesData): array
    {
        if (ArrayUtils::isMultidimentional($valuesData)) {
            // Batch insert
            return $this->combineColumnsWithBatchValues($columns, $valuesData);
        } else {
            // Single insert - wrap in array for consistency
            return [$this->combineColumnsWithValuesSingle($columns, $valuesData)];
        }
    }

    public function combineColumnsWithValuesSingle(array $columns, array $values): array
    {
        if (count($columns) !== count($values)) {
            throw new InvalidArgumentException(
                sprintf('Columns count (%d) does not match values count (%d)', count($columns), count($values)),
            );
        }

        return array_combine($columns, $values);
    }

    public function combineColumnsWithBatchValues(array $columns, array $batchValues): array
    {
        $result = [];

        foreach ($batchValues as $index => $values) {
            $result[] = $this->combineColumnsWithValuesSingle($columns, $values);
        }

        return $result;
    }

    private function normalizeValues(array $valuesData): array
    {
        $isBatchInsert = ArrayUtils::isMultidimentional($valuesData) &&
                    ArrayUtils::isSequential($valuesData) &&
                    is_array(ArrayUtils::first($valuesData));

        if ($isBatchInsert) {
            $firstRowCount = count(ArrayUtils::first($valuesData));
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