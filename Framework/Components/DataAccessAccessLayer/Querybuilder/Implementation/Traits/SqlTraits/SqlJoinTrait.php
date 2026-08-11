<?php

declare(strict_types=1);
trait SqlJoinTrait
{
    protected array $joinMap = [];
    protected array $onConditions = [];
    private array $standardizedOnConditions = [];

    public function join(
        string|SqlSelectQueryBuilderInterface|Closure $table,
        null|string|array $params = null,
    ): static {
        return $this->addJoin('join', $table, $params);
    }

    public function leftJoin(string|SqlSelectQueryBuilderInterface|Closure $table, null|string|array $params = null): static
    {
        return $this->addJoin('leftJoin', $table, $params);
    }

    public function rightJoin(string|SqlSelectQueryBuilderInterface|Closure $table, null|string|array $params = null): static
    {
        return $this->addJoin('rightJoin', $table, $params);
    }

    public function innerJoin(string|SqlSelectQueryBuilderInterface|Closure $table, null|string|array $params = null): static
    {
        return $this->addJoin('innerJoin', $table, $params);
    }

    public function on(mixed ...$onConditions): static
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
        $innerMethod = $onConditions['innerMethod'] ?? __FUNCTION__;
        $innerData = $onConditions['innerData'] ?? $onConditions;
        $standardized = $standardizer->standardize($innerData);

        $this->standardizedOnConditions[$this->currentTable][] = $standardized->getConditions();

        $this->onConditions[$this->currentTable][] = [
            'onConditions' => $standardized->getConditions(), //$this->standardizedOnConditions[$this->currentTable],
            'joinContext' => $this->currentTable,
            'fromTable' => $this->table,
            'toTable' => $this->currentTable,
            'method' => $innerMethod,
        ];

        $this->queryFlow[] = $innerMethod;
        $this->method = $innerMethod;
        return $this;
    }

    public function andOn(mixed ...$onConditions): static
    {
        return $this->on(...['innerData' => $onConditions, 'innerMethod' => __FUNCTION__]);
    }

    public function onValue(mixed ...$onConditions): static
    {
        return $this->on(...['innerData' => $onConditions, 'innerMethod' => __FUNCTION__]);
    }

    public function orOnValue(mixed ...$onConditions): static
    {
        return $this->on(...['innerData' => $onConditions, 'innerMethod' => __FUNCTION__]);
    }

    #[Override]
    public function onEqualTo(string $leftCol, string $rightCol): static
    {
        throw new Exception('Not implemented');
    }

    #[Override]
    public function onNotEqualTo(string $leftCol, string $rightCol): static
    {
        throw new Exception('Not implemented');
    }

    private function addJoin(string $type, string|Closure $table, mixed $params = null): static
    {
        $this->ensureFromExists();
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
        $this->queryFlow[] = $type;

        return $this;
    }
}