<?php

declare(strict_types=1);
class QueryFlowValidatorForUpdate implements FlowValidatorInterface
{
    public function __construct(private SqlUpdateQuery $query)
    {
    }

    public function validate(array $queryFlow, array $updateMap, array $conditions = []): void
    {
        $this->validateRequiredClauses($queryFlow);
        $this->validateUpdateMap($updateMap);
    }

    private function validateRequiredClauses(array $queryFlow): void
    {
        $update = isset($queryFlow['update']) || isset($queryFlow['bulkUpdate']);
        if (!$update) {
            throw new QueryFlowException('UPDATE query requires update statement');
        }
        if (!isset($queryFlow['set'])) {
            throw new QueryFlowException('UPDATE query requires set clause');
        }
    }

    private function validateUpdateMap(array $updateMap): void
    {
        if (ArrayUtils::isDeepEmpty($updateMap)) {
            $this->query->assumeEntityManagerHasUpdateData();
            return;
        }

        list($table, $setData, $columnsData, $valuesData) = $this->query->getUpdateMapFragments($updateMap);

        if ($table === null) {
            $this->query->assumeupdateCurrentTable();
        }
        if ($columnsData && $valuesData === null) {
            throw new QueryFlowException('No Values defnined for columns : ' . implode(', ', $columnsData));
        }
        if ($setData && $columnsData && ArrayUtils::isStringList($setData)) {
            throw new QueryFlowException('Unable to insert Data that aree defined twice. insert data :' . implode(', ', $setData) . ' and : ' . implode(', ', $columnsData));
        }
        if ($columnsData && $valuesData) {
            $this->validateColumnsValuesPair($columnsData, $valuesData);
        }
        if ($setData === null && $columnsData === null && $valuesData === null) {
            $this->query->assumeEntityManagerHasUpdateData();
        }
    }

    private function validateColumnsValuesPair(array $columns, array $values): void
    {
        $columnCount = count($columns);
        $valueCount = count($values);

        if ($columnCount === 0) {
            throw new InvalidArgumentException('No columns provided');
        }

        if ($valueCount === 0) {
            throw new InvalidArgumentException('No values provided');
        }

        if ($columnCount !== $valueCount) {
            throw new InvalidArgumentException(
                "Column-value mismatch. Expected $columnCount values, got $valueCount",
            );
        }
    }
}
