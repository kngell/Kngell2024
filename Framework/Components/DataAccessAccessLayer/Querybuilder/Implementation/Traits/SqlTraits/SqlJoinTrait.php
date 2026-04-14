<?php

declare(strict_types=1);
trait SqlJoinTrait
{
    protected array $joinMap = [];
    protected array $onConditions = [];

    public function join(
        string|SqlSelectQueryBuilderInterface|Closure $table,
        null|string|array $params = null,
    ): self {
        return $this->addJoin('join', $table, $params);
    }

    public function leftJoin(string|SqlSelectQueryBuilderInterface|Closure $table, null|string|array $params = null): self
    {
        return $this->addJoin('leftJoin', $table, $params);
    }

    public function rightJoin(string|SqlSelectQueryBuilderInterface|Closure $table, null|string|array $params = null): self
    {
        return $this->addJoin('rightJoin', $table, $params);
    }

    public function innerJoin(string|SqlSelectQueryBuilderInterface|Closure $table, null|string|array $params = null): self
    {
        return $this->addJoin('innerJoin', $table, $params);
    }

    public function on(mixed ...$onConditions): self
    {
        $standardizer = $this->getClauseStandardizer(__FUNCTION__);

        if (!$standardizer) {
            throw new RuntimeException('No standardizer found for ON clause');
        }

        if ($standardizer instanceof OnDataStandardizer) {
            $standardizer
                ->setLogicalTable($this->table)
                ->setHelper($this->helper);
        }

        $payload = $standardizer->standardize($onConditions);

        $this->onConditions[$this->currentTable] = [
            'onConditions' => $payload->getConditions(),
            'joinContext' => $this->currentTable,
            'fromTable' => $this->table,
            'toTable' => $this->currentTable,
        ];

        $this->queryFlow['on'] = true;
        $this->method = __FUNCTION__;
        return $this;
    }

    public function onEqualTo(string $leftColumn, string $rightColumn): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function onNotEqualTo(string $leftColumn, string $rightColumn): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    private function addJoin(string $type, string|Closure $table, mixed $params = null): self
    {
        $isUpdateQuery = $this instanceof SqlUpdateQueryBuilderInterface;

        // Handle UPDATE special case: empty table, query in params
        if ($isUpdateQuery && ($table === '' || $table === null) &&
            ($params instanceof SqlSelectQueryBuilderInterface || $params instanceof Closure)) {
            $originalTable = $params;      // The actual query
            $actualParams = null;          // No columns for UPDATE
            $tableForUnique = 'subquery';  // Base identifier
        } else {
            // Normal case (SELECT or UPDATE with regular table)
            $originalTable = $table;       // String, query, or closure
            $actualParams = $params;       // Columns array or null

            // Convert to string identifier
            if ($table instanceof SqlSelectQueryBuilderInterface) {
                $tableForUnique = 'subquery';
            } elseif ($table instanceof Closure) {
                $tableForUnique = 'closure';
            } else {
                $tableForUnique = $table;
            }
        }

        if (empty($tableForUnique)) {
            throw new QueryFlowException('The joined table cannot be null');
        }

        // Your fixed getUniqueTableName ensures unique logical names
        [$uniqueTableName, $key] = $this->getUniqueTableName($type, $tableForUnique, $this->queryMap);

        $this->currentTable = $uniqueTableName;

        // Build join data
        $joinData = [
            'table' => $uniqueTableName,
            'columns' => is_array($params) ? $params : [],
            'method' => $type,
            'withAlias' => $this->withAlias,
            'customAlias' => is_string($params) ? $params : null,
        ];

        // Handle columns array for SELECT queries
        if (!$isUpdateQuery && is_array($actualParams)) {
            $joinData['columns'] = $actualParams;
        }

        // Store original query/closure for building
        if ($originalTable instanceof SqlSelectQueryBuilderInterface) {
            $joinData['query'] = $originalTable;
        } elseif ($originalTable instanceof Closure) {
            $joinData['closure'] = $originalTable;
        }

        // Store in maps
        $this->joinMap[$key] = $joinData;
        $this->queryMap[] = $uniqueTableName;
        $this->queryFlow[$type] = true;

        return $this;
    }
}