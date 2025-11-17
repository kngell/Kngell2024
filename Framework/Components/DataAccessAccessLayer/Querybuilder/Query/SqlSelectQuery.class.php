<?php

declare(strict_types=1);

class SqlSelectQuery extends SqlQuery implements SqlSelectQueryBuilderInterface
{
    private ColumnCollector $columnCollector;
    private array $selectMap = [];
    private array $joinMap = [];
    private array $onConditions = [];
    private array $conditionsMap = [];
    private ?string $currentTable = null;
    private bool $isTableResolved = false;
    private bool $isClosure = false;
    private array $groupByColumns = [];
    private array $orderByColumns = [];
    private int $limitValue;
    private int $offsetValue;
    private array $columns = [];
    private array $havingConditions = [];

    public function __construct(
        EntityManagerInterface $em,
    ) {
        parent::__construct(SqlStatementType::SELECT, $em, $this->columns);
        $this->initializeWithDependencies($em->getTableAliasHelper(), $this->getState());
    }

    public function build(): string
    {
        $this->initializeComponents();

        $this->flowValidator->validate($this->queryFlow, $this->joinMap, $this->onConditions);

        $this->clauseBuilder->buildAllClauses();

        return parent::build();
    }

    public function select(mixed ...$columns): self
    {
        $this->columns = $columns;

        $this->selectMap['select'] = [
            'table' => null,
            'columns' => ArrayUtils::first($columns),
            'withAlias' => $this->withAlias,
            'customAlias' => null,
        ];
        $this->queryFlow['select'] = true;
        $this->currentTable = $this->table;
        return $this;
    }

    public function from(null|string|Closure $table = null, ?string $alias = null): self
    {
        $this->queryFlow['from'] = true;
        $entity = $this->em->getEntity();
        $this->table = $table ?? $this->resolveMainTable($entity);
        $this->customAlias = $alias;
        $this->selectMap['select']['table'] = $this->table;
        $this->selectMap['select']['customAlias'] = $alias;
        $this->isTableResolved = true;
        $this->currentTable = $table;
        return $this;
    }

    // DRY join methods
    public function leftJoin(string|Closure $table, null|string|array $params = null): self
    {
        return $this->addJoin('leftJoin', $table, $params);
    }

    public function rightJoin(string|Closure $table, null|string|array $params = null): self
    {
        return $this->addJoin('rightJoin', $table, $params);
    }

    public function innerJoin(string|Closure $table, null|string|array $params = null): self
    {
        return $this->addJoin('innerJoin', $table, $params);
    }

    public function on(mixed ...$onConditions): self
    {
        $this->onConditions[$this->currentTable] = [
            'onConditions' => $onConditions,
            'joinContext' => $this->currentTable,
        ];
        $this->queryFlow['on'] = true;
        return $this;
    }

    // Condition methods
    public function where(mixed ...$conditions): self
    {
        if (!isset($this->queryFlow['from']) && isset($this->queryFlow['select'])) {
            $this->from();
        }

        if (!empty(ArrayUtils::flattenArrayRecursive($conditions))) {
            if (!isset($this->conditionsMap['where'])) {
                $this->conditionsMap['where'] = [];
            }

            $this->conditionsMap['where'][] = [
                'method' => __FUNCTION__,
                'conditions' => $conditions,
            ];
            $this->queryFlow['where'] = true;
        }

        return $this;
    }

    public function orWhere(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        return $this;
    }

    public function or(mixed ...$conditions): self
    {
        return $this->orWhere(...$conditions);
    }

    public function andWhere(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        $this->queryFlow['andWhere'] = true;
        return $this;
    }

    public function and(mixed ...$conditions): self
    {
        return $this->andWhere(...$conditions);
    }

    public function whereIn(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        $this->queryFlow['whereIn'] = true;
        return $this;
    }

    public function whereNotIn(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        $this->queryFlow['whereNotIn'] = true;
        return $this;
    }

    public function orWhereIn(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        return $this;
    }

    public function orWhereNotIn(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        return $this;
    }

    public function join(
        string|Closure $table,
        null|string|array $params = null,
    ): SqlSelectQueryBuilderInterface {
        throw new Exception('Not implemented');
    }

    public function whereEqualTo(string $column, mixed $value): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereNotEqualTo(string $column, mixed $value): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereLessThan(string $column, mixed $value): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereGreaterThan(string $column, mixed $value): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereLike(string $column, string $pattern): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereNotLike(string $column, string $pattern): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereNull(string $column): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereNotNull(string $column): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function whereBetween(string $column, mixed $min, mixed $max): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function onEqualTo(string $leftColumn, string $rightColumn): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function onNotEqualTo(string $leftColumn, string $rightColumn): SqlSelectQueryBuilderInterface
    {
        throw new Exception('Not implemented');
    }

    public function execute(): array
    {
        throw new Exception('Not implemented');
    }

    public function getStatementType(): SqlStatementType
    {
        return $this->sqlClause;
    }

    public function withAlias(bool $withAlias = true): self
    {
        $this->state = $this->state->withAlias($withAlias);
        return $this;
    }

    // HAVING condition methods (if needed)
    public function having(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        $this->queryFlow['having'] = true;
        return $this;
    }

    public function orHaving(mixed ...$conditions): self
    {
        if (!isset($this->conditionsMap['where'])) {
            $this->conditionsMap['where'] = [];
        }

        $this->conditionsMap['where'][] = [
            'method' => __FUNCTION__,
            'conditions' => $conditions,
        ];
        return $this;
    }

    public function groupBy(string ...$columns): self
    {
        $this->queryFlow['groupBy'] = true;
        $this->groupByColumns = $columns;
        return $this;
    }

    public function orderBy(string ...$columnsDirections): self
    {
        $this->queryFlow['orderBy'] = true;
        $this->orderByColumns = $columnsDirections;
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->queryFlow['limit'] = true;
        $this->limitValue = $limit;
        return $this;
    }

    public function offset(int $offset): self
    {
        $this->queryFlow['offset'] = true;
        $this->offsetValue = $offset;
        return $this;
    }

    public function getGroupByColumns(): array
    {
        return $this->groupByColumns ?? [];
    }

    public function getHavingConditions(): array
    {
        return $this->havingConditions ?? [];
    }

    public function getOrderByColumns(): array
    {
        return $this->orderByColumns ?? [];
    }

    public function getLimitValue(): ?int
    {
        return $this->limitValue ?? null;
    }

    public function getWithAlias(): bool
    {
        return $this->state->withAlias;
    }

    public function getOffsetValue(): ?int
    {
        return $this->offsetValue ?? null;
    }

    public function assumeFromCurrentTable(): void
    {
        $entity = $this->em->getEntity();
        if (!$this->hasFrom()) {
            $this->from($this->resolveMainTable($entity));
        }
    }

    public function assumeAllColumns(): void
    {
        if (!$this->hasSelect()) {
            $this->select('*');
        }
    }

    public function getFlowDiagnostics(): string
    {
        $userFlow = array_keys($this->queryFlow);
        $expectedOrder = SqlStatementType::SELECT->getBuildOrder();

        return sprintf(
            "User flow: [%s]\nExpected order: [%s]",
            implode(' → ', $userFlow),
            implode(' → ', $expectedOrder),
        );
    }

    public function getState(): QueryState
    {
        return $this->state;
    }

    public function getTable(): string
    {
        $entity = $this->em->getEntity();
        if (!$this->isTableResolved) {
            $this->table = $this->resolveMainTable($entity);
            $this->isTableResolved = true;
        }

        return $this->table;
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }

    public function getSelectData(): ?array
    {
        if (!isset($this->selectMap['select'])) {
            return null;
        }

        return [
            'columns' => $this->columnCollector->all(), //$this->collectAllColumns(),
            'withAlias' => $this->withAlias,
            'tableAliasHelper' => $this->em->getTableAliasHelper(),
        ];
    }

    public function getFromData(): ?array
    {
        if (!isset($this->queryFlow['from'])) {
            return null;
        }

        return [
            'table' => $this->table,
            'columns' => $this->columns,
        ];
    }

    public function getWhereData(): array
    {
        return $this->conditionsMap;
    }

    public function getJoinData(): array
    {
        return [
            'joinMap' => $this->joinMap,
            'onConditions' => $this->onConditions,
        ];
    }

    public function hasWhereConditions(): bool
    {
        return !empty($this->conditionsMap);
    }

    public function hasSelect(): bool
    {
        return isset($this->queryFlow['select']);
    }

    public function hasFrom(): bool
    {
        return isset($this->queryFlow['from']);
    }

    public function hasWhere(): bool
    {
        return !empty($this->conditionsMap);
    }

    public function hasJoins(): bool
    {
        return !empty($this->joinMap);
    }

    public function hasOnConditionsForTable(string $tableName): bool
    {
        return isset($this->onConditions[$tableName]);
    }

    public function hasGroupBy(): bool
    {
        return isset($this->queryFlow['groupBy']);
    }

    public function hasHaving(): bool
    {
        return isset($this->queryFlow['having']);
    }

    public function hasOrderBy(): bool
    {
        return isset($this->queryFlow['orderBy']);
    }

    public function hasLimit(): bool
    {
        return isset($this->queryFlow['limit']);
    }

    public function hasOffset(): bool
    {
        return isset($this->queryFlow['offset']);
    }

    public function getSelectColumns(): array
    {
        return $this->columnCollector->all();
        // return $this->collectAllColumns();
    }

    public function getFromTable(): ?string
    {
        return $this->table;
    }

    public function getFromColumns(): array
    {
        return $this->columns;
    }

    public function getWhereConditions(): array
    {
        return $this->conditionsMap;
    }

    public function getOnConditionsForTable(string $tableName): array
    {
        return $this->onConditions[$tableName] ?? [];
    }

    public function getTableAliasHelper(): TablesAliasHelper
    {
        return $this->em->getTableAliasHelper();
    }

    public function getOnConditions(): array
    {
        return $this->onConditions;
    }

    /**
     * @return array
     */
    public function getJoinMap(): array
    {
        return $this->joinMap;
    }

    /**
     * @param bool $isClosure
     *
     * @return SqlSelectQuery
     */
    public function setClosureState(bool $isClosure = true): SqlSelectQuery
    {
        $this->isClosure = $isClosure;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClosure(): bool
    {
        return $this->isClosure;
    }

    // protected function collectAllColumns(): array
    // {
    //     $columnMap = [];

    //     // Add main SELECT columns
    //     if (isset($this->selectMap['select'])) {
    //         $selectConfig = $this->selectMap['select'];
    //         $table = $selectConfig['table'] ?? 'main';

    //         $columnMap[$table] = [
    //             'columns' => $selectConfig['columns'],
    //             'customAlias' => $selectConfig['customAlias'],
    //             'withAlias' => $selectConfig['withAlias'],
    //         ];
    //     }

    //     // Add JOIN columns
    //     foreach ($this->joinMap as $joinKey => $joinConfig) {
    //         $table = $joinConfig['table'];

    //         if (isset($columnMap[$table])) {
    //             $columnMap[$table]['columns'] = array_merge(
    //                 $columnMap[$table]['columns'],
    //                 $joinConfig['columns'],
    //             );
    //         } else {
    //             $columnMap[$table] = [
    //                 'columns' => $joinConfig['columns'],
    //                 'customAlias' => $joinConfig['customAlias'],
    //                 'withAlias' => $joinConfig['withAlias'],
    //             ];
    //         }
    //     }

    //     return $columnMap;
    // }

    // private function updateState(?callable $updater = null): void
    // {
    //     if ($updater === null || !is_callable($updater)) {
    //         return;
    //     }
    //     $this->state = ($updater)($this->state);
    // }

    protected function initializeComponents(): void
    {
        $this->columnCollector = new ColumnCollector($this->selectMap, $this->joinMap);
        parent::initializeComponents();
    }

    private function addJoin(string $type, string|Closure $table, null|string|array $params = null): self
    {
        if (empty($table)) {
            throw new QueryFlowException('The joined table cannot be null');
        }

        $key = $type . '|' . (is_string($table) ? $table : spl_object_hash($table));

        if (array_key_exists($key, $this->joinMap)) {
            throw new QueryFlowException('The table is already joined');
        }

        $this->joinMap[$key] = [
            'table' => $table,
            'columns' => is_array($params) ? $params : [],
            'withAlias' => $this->withAlias,
            'customAlias' => is_string($params) ? $params : null,
        ];

        $this->queryFlow[$type] = true;
        $this->currentTable = $table;
        return $this;
    }
}