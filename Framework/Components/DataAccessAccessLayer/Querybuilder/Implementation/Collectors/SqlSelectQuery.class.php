<?php

declare(strict_types=1);

class SqlSelectQuery extends SqlQuery implements SqlSelectQueryBuilderInterface
{
    use SqlWhereConditionTrait;
    use SqlHavingTrait;
    use SqlJoinTrait;
    // use SqlCteTrait;
    use SqlQueryStructureTrait;
    use SqlSelectQueryGettersAndSettersTrait;

    private const SqlStatement TYPE = SqlStatement::SELECT;

    private ColumnCollector $columnCollector;
    private array $selectMap = [];
    private array $conditionsMap = [];
    private bool $isTableResolved = false;
    private array $groupByMap = [];
    private array $orderByColumns = [];
    private array $limitMap = [];
    private array $offsetMap = [];
    private array $columns = [];
    private array $havingConditions = [];
    private array $cteMap = [];
    private array $unionMap = [];
    private ?bool $isRecursive = false;
    private ?StatementType $context = null;

    public function __construct(
        EntityManagerInterface $em,
        private bool $isBulkQuery = false,
    ) {
        $this->method = self::TYPE->value;
        parent::__construct(null, self::TYPE, $em, $this->columns);
        $this->initializeWithDependencies($em->getTableAliasHelper(), $this->getState());
        $this->initializeComponents();
    }

    public function build(): string
    {
        $this->columnCollector->setSelectMap($this->selectMap)->setJoinMap($this->joinMap);
        $this->flowValidator->validate($this->queryFlow, $this->joinMap, $this->onConditions);
        $this->clauseBuilder->buildAllClauses(self::TYPE);
        return parent::build();
    }

    public function select(mixed ...$columns): self
    {
        $this->columns = $columns;

        $this->selectMap = [
            'table' => null,
            'columns' => $this->standardizer->setMethod(__FUNCTION__)->standardize($columns)->getColumns(),
            'withAlias' => $this->state->withAlias,
            'distinct' => $this->state->distinct,
            'customAlias' => null,
            'method' => __FUNCTION__,
        ];
        $this->queryFlow['select'] = true;
        $this->currentTable = $this->table;
        $this->method = __FUNCTION__;
        if (!$this->entryMethod === null) {
            $this->entryMethod = __FUNCTION__;
        }
        return $this;
    }

    public function distinct(bool $enable = true): self
    {
        $this->distinct = $enable;
        $this->state = $this->state->distinct($enable);
        return $this;
    }

    public function from(mixed $table = null, ?string $alias = null): self
    {
        $isTable = is_string($table) || is_null($table);
        if (!$isTable) {
            $data = $table;
            $table = 'virtualTable';
        }
        $entity = $this->em->getEntity();

        $resolvedTable = $table ?? $this->resolveMainTable($entity);

        list($table, $key) = $this->getUniqueTableName(__FUNCTION__, $resolvedTable, $this->queryMap);

        $this->customAlias = $alias;

        if ($this->isBulkQuery && !$isTable) {
            $data = fn () => $data ?? [];
        }
        $this->table = $table;

        $this->selectMap['table'] = $table;
        $this->selectMap['customAlias'] = $alias;
        if (isset($data)) {
            $this->selectMap['data'] = $data;
        }

        $this->queryMap[] = $table;
        $this->queryFlow[__FUNCTION__] = true;

        $this->isTableResolved = true;
        $this->currentTable = $table;

        return $this;
    }

    public function withAlias(bool $withAlias = true): self
    {
        $this->withAlias = $withAlias;
        $this->state = $this->state->withAlias($withAlias);
        return $this;
    }

    public function unionAll(SqlSelectQuery|Closure $query): self
    {
        $query->setParent($this);
        $this->unionMap[] = [
            'method' => __FUNCTION__,
            'query' => $query,
        ];
        $this->queryFlow[__FUNCTION__] = true;
        return $this;
    }

    public function getStatement(): SqlStatement
    {
        return self::TYPE;
    }

    public function getHavingConditions(): array
    {
        return $this->havingConditions ?? [];
    }

    public function getOrderByColumns(): array
    {
        return $this->orderByColumns ?? [];
    }

    public function getWithAlias(): bool
    {
        return $this->state->withAlias;
    }

    public function assumeFromCurrentTable(): void
    {
        $entity = $this->em->getEntity();
        if (!$this->hasFrom()) {
            $this->from($this->resolveMainTable($entity));
            $this->columnCollector->setSelectMap($this->selectMap);
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
        $expectedOrder = SqlStatement::SELECT->getBuildOrder();

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

    public function getSelectData(): ?array
    {
        if (!isset($this->selectMap['select'])) {
            return null;
        }

        return [
            'columns' => $this->columnCollector->all(),
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
    }

    public function getFromTable(): null|string|Closure
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

    /**
     * @return array
     */
    public function getLimitMap(): array
    {
        return $this->limitMap;
    }

    /**
     * @return array
     */
    public function getOffsetMap(): array
    {
        return $this->offsetMap;
    }

    /**
     * @return array
     */
    public function getGroupByMap(): array
    {
        return $this->groupByMap;
    }

    /**
     * @return array
     */
    public function getCteMap(): array
    {
        return $this->cteMap;
    }

    /**
     * @return null|bool
     */
    public function isRecursive(): ?bool
    {
        return $this->isRecursive;
    }

    /**
     * @return array
     */
    public function getUnionMap(): array
    {
        return $this->unionMap;
    }

    public function hasUnion(): bool
    {
        return isset($this->queryFlow['unionAll']) || isset($this->queryFlow['union']);
    }

    /**
     * @return array
     */
    public function getSelectMap(): array
    {
        return $this->selectMap;
    }

    /**
     * @return null|StatementType
     */
    public function getContext(): ?StatementType
    {
        return $this->context;
    }

    /**
     * @param null|StatementType $context
     *
     * @return SqlSelectQuery
     */
    public function setContext(?StatementType $context): SqlSelectQuery
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return ColumnCollector
     */
    public function getColumnCollector(): ColumnCollector
    {
        return $this->columnCollector;
    }

    protected function initializeComponents(): void
    {
        $this->columnCollector = new ColumnCollector($this->selectMap, $this->joinMap);
        parent::initializeComponents();
    }
}