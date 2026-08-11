<?php

declare(strict_types=1);

class SqlSelectQuery extends SqlQuery implements SqlSelectQueryBuilderInterface
{
    use SqlWhereConditionTrait;
    use SqlHavingTrait;
    use SqlJoinTrait;
    use SqlQueryStructureTrait;
    use SqlSelectQueryGettersAndSettersTrait;

    private const SqlStatement TYPE = SqlStatement::SELECT;

    private ColumnCollector $columnCollector;
    private array $selectMap = [];
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

    public function __construct(
        EntityManagerInterface $em,
        private bool $isBulkQuery = false,
    ) {
        $this->method = self::TYPE->value;
        parent::__construct(null, self::TYPE, $em);
        $this->initializeWithDependencies($em->getTableAliasHelper(), $this->getState());
        $this->initializeComponents();
    }

    public function build(): string
    {
        $this->validateSelect();
        $this->columnCollector->setSelectMap($this->selectMap)->setJoinMap($this->joinMap);
        $this->buildStatement();
        return parent::build();
    }

    public function select(mixed ...$columns): static
    {
        $this->columns = $columns;

        $this->selectMap = [
            'table' => null,
            'columns' => $this->standardizer->setMethod(__FUNCTION__)->standardize($columns)->getColumns(),
            'withAlias' => $this->state->withAlias,
            'distinct' => $this->state->distinct,
            'distinctCount' => $this->distinctCount,
            'customAlias' => null,
            'method' => __FUNCTION__,
        ];
        $this->queryFlow[] = 'select';
        $this->currentTable = $this->table;
        $this->method = __FUNCTION__;
        if (!$this->entryMethod === null) {
            $this->entryMethod = __FUNCTION__;
        }
        return $this;
    }

    public function distinct(bool $enable = true): static
    {
        $this->distinct = $enable;
        $this->state = $this->state->distinct($enable);
        return $this;
    }

    public function distinctCount(bool $enable = true): static
    {
        $this->distinctCount = $enable;
        return $this;
    }

    public function from(mixed $table = null, ?string $alias = null): static
    {
        $isTable = is_string($table) || is_null($table);
        $data = null;
        if (!$isTable) {
            $data = $table;
            $table = 'virtualTable';
            if ($data instanceof SqlSelectQuery) {
                $data->setParent($this);
                $safeUniqueAlias = 'vt_' . md5(uniqid((string) rand(), true));
                $data->addCustomAlias($safeUniqueAlias);
            }
        }

        $resolvedTable = $table ?? $this->resolveMainTable();

        list($table, $key) = $this->getUniqueTableName(__FUNCTION__, $resolvedTable, $this->queryMap);

        $this->customAlias = $alias;

        if (($this->isBulkQuery || $data instanceof SqlSelectQuery) && !$isTable) {
            $data = fn () => $data ?? [];
        }
        $this->table = $table;

        $this->selectMap['table'] = $table;
        $this->selectMap['customAlias'] = $alias;
        if (!empty($data)) {
            $this->selectMap['data'] = $data;
        }

        $this->queryMap[] = $table;
        $this->queryFlow[] = __FUNCTION__;

        $this->isTableResolved = true;
        $this->currentTable = $table;

        return $this;
    }

    public function withAlias(bool $withAlias = true): static
    {
        $this->withAlias = $withAlias;
        $this->state = $this->state->withAlias($withAlias);
        return $this;
    }

    public function unionAll(SqlSelectQuery|Closure $query): static
    {
        $query->setParent($this);
        $this->unionMap[] = [
            'method' => __FUNCTION__,
            'query' => $query,
        ];
        $this->queryFlow[] = __FUNCTION__;
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
            $this->from($this->resolveMainTable());
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
        $userFlow = $this->queryFlow;
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
        if (!$this->isTableResolved) {
            $this->table = $this->resolveMainTable();
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
        if (!ArrayUtils::hasValue($this->queryFlow, 'from')) {
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
        return ArrayUtils::hasValue($this->queryFlow, 'select');
    }

    public function hasFrom(): bool
    {
        return ArrayUtils::hasValue($this->queryFlow, 'from');
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
        return ArrayUtils::hasValue($this->queryFlow, 'groupBy');
    }

    public function hasHaving(): bool
    {
        return ArrayUtils::hasValue($this->queryFlow, 'having');
    }

    public function hasOrderBy(): bool
    {
        return ArrayUtils::hasValue($this->queryFlow, 'orderBy');
    }

    public function hasLimit(): bool
    {
        return ArrayUtils::hasValue($this->queryFlow, 'limit');
    }

    public function hasOffset(): bool
    {
        return ArrayUtils::hasValue($this->queryFlow, 'offset');
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
        return ArrayUtils::hasValue($this->queryFlow, 'unionAll') || ArrayUtils::hasValue($this->queryFlow, 'union');
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

    public function addCustomAlias(string $customAlias): static
    {
        if ($this->selectMap['customAlias'] === null) {
            $this->selectMap['customAlias'] = $customAlias;
        }
        return $this;
    }

    protected function initializeComponents(): void
    {
        $this->columnCollector = new ColumnCollector($this->selectMap, $this->joinMap);
        parent::initializeComponents();
    }

    private function validateSelect(): void
    {
        if ($this->hasSelect() && !$this->hasFrom()) {
            $this->assumeFromCurrentTable();
        }

        // If user has from() but no select(), assume all columns
        if ($this->hasFrom() && !$this->hasSelect()) {
            $this->assumeAllColumns();
        }

        // Validate we have at least the minimal required
        if (!$this->isClosure() && (!$this->hasSelect() || !$this->hasFrom())) {
            throw new QueryFlowException(
                'Query must have at least SELECT and FROM clauses. ' .
                'Called select(): ' . ($this->hasSelect() ? 'yes' : 'no') . ', ' .
                'Called from(): ' . ($this->hasFrom() ? 'yes' : 'no'),
            );
        }
    }

    private function buildStatement(): void
    {
        $statement = new SelectStatement(
            columnCollector: $this->columnCollector,
            selectMap:[
                'select' => $this->selectMap,
                'join' => $this->joinMap,
                'on' => $this->onConditions,
                'where' => $this->conditionsMap,
                'having' => $this->havingConditions,
                'group_by' => $this->groupByMap,
                'order_by' => $this->orderByColumns,
                'limit' => $this->limitMap,
                'offset' => $this->offsetMap,
            ],
            queryFlow: $this->queryFlow,
            em: $this->em,
        );

        // $statement->setHelper($this->helper);
        $this->add($statement);
    }
}