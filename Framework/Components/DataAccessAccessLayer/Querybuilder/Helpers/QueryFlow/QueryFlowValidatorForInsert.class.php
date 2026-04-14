<?php

declare(strict_types=1);
class QueryFlowValidatorForInsert implements FlowValidatorInterface
{
    public function __construct(private SqlInsertQuery $query)
    {
    }

    public function validate(array $queryFlow, array $insertMap, array $xonditions = []): void
    {
        $this->validateRequiredClauses($queryFlow);
        // $this->validateInsertMap($insertMap);
    }

    private function validateRequiredClauses(array $queryFlow): void
    {
        $statement = $this->query->getStatement();
        $requiredClauses = $statement->getRequiredClauses();
        $flow = array_keys($queryFlow);
        foreach ($requiredClauses as $clause) {
            if ($clause === 'into' && !in_array($clause, $flow)) {
                $this->query->assumeInsertIntoCurrentTable();
                continue;
            }
            if ($clause === 'values' && !in_array($clause, $flow)) {
                $this->query->assumeEntityManagerHasInsertData();
                continue;
            }
            if (!in_array($clause, $flow)) {
                throw new QueryFlowException("Missing required clause '$clause' in the Insert query flow");
            }
        }
        // if (!isset($queryFlow['insert'])) {
        //     throw new QueryFlowException('INSERT query requires insert statement');
        // }
    }

    private function validateInsertMap(array $insertMap): void
    {
        if (ArrayUtils::isDeepEmpty($insertMap)) {
            $this->query->assumeEntityManagerHasInsertData();
            return;
        }

        list($table, $insertData, $columnsData, $valuesData) = $this->query->getInsertMapFragments($insertMap);

        if ($table === null) {
            $this->query->assumeInsertIntoCurrentTable();
        }
        if ($columnsData && $valuesData === null) {
            throw new QueryFlowException('No Values defnined for columns : ' . implode(', ', $columnsData));
        }
        if ($insertData && $columnsData) {
            throw new QueryFlowException('Unable to insert Data that aree defined twice. insert data :' . implode(', ', $insertData) . ' and : ' . implode(', ', $columnsData));
        }
        if ($columnsData && $valuesData) {
            $this->validateColumnsValuesPair($columnsData, $valuesData);
        }
        if ($insertData === null && $columnsData === null && $valuesData === null) {
            $this->query->assumeEntityManagerHasInsertData();
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
