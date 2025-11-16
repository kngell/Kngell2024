<?php

declare(strict_types=1);
class QueryFlowValidatorForSelect implements FlowValidatorInterface
{
    public function __construct(private SqlQueryComponent $query)
    {
    }

    public function validate(array $queryFlow, array $joinMap, array $onConditions = []): void
    {
        $this->validateRequiredClauses($queryFlow);
        $this->validateJoinOnPairs($joinMap, $onConditions);
    }

    private function validateRequiredClauses(array $queryFlow): void
    {
        if (!isset($queryFlow['from']) && isset($queryFlow['select'])) {
            throw new QueryFlowException('SELECT query requires FROM clause');
        }
    }

    private function validateJoinOnPairs(array $joinMap, array $onConditions): void
    {
        $joinTables = array_keys($joinMap);

        foreach ($joinTables as $joinedTable) {
            $tableName = $this->extractTableName($joinedTable);
            if (!isset($onConditions[$tableName])) {
                throw new QueryFlowException("JOIN clause for table '{$tableName}' requires ON clause");
            }
        }

        foreach (array_keys($onConditions) as $onTable) {
            if (!$this->hasMatchingJoin($onTable, $joinTables)) {
                throw new QueryFlowException("ON clause for table '{$onTable}' has no JOIN clause");
            }
        }
    }

    private function extractTableName(string $joinKey): string
    {
        return explode('|', $joinKey)[1] ?? $joinKey;
    }

    private function hasMatchingJoin(string $table, array $joinTables): bool
    {
        foreach ($joinTables as $joinedTable) {
            if (str_contains($joinedTable, $table)) {
                return true;
            }
        }
        return false;
    }
}