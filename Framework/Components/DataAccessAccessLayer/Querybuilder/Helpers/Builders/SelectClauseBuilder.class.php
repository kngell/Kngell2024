<?php

declare(strict_types=1);

class SelectClauseBuilder extends AbstractClauseBuilder implements ClauseBuilderInterface
{
    public function __construct(
        protected SqlSelectQuery $query,
    ) {
    }

    protected function buildStatement(?SqlStatement $type = null): void
    {
        foreach ($type->getBuildOrder() as $clause) {
            if ($this->shouldBuildClause($clause)) {
                $this->buildClause($clause);
            }
        }
    }

    protected function shouldBuildClause(string $clause): bool
    {
        $userFlow = array_keys($this->query->getQueryFlow());
        return in_array($clause, $userFlow);
    }

    protected function validateClauseOrder(): void
    {
        $userFlow = array_keys($this->query->getQueryFlow());
        $statementType = $this->query->getStatement();
        $categoryOrder = $statementType->getCategoryBuildOrder();

        $this->validateAllowedMethods($userFlow, $statementType);
        $this->validateCategoryOrder($userFlow, $categoryOrder);
        $this->validateJoinOnPairs();
    }

    protected function ensureMinimalFlow(): void
    {
        // If user called select() but no from(), assume current table
        if ($this->query->hasSelect() && !$this->query->hasFrom()) {
            $this->query->assumeFromCurrentTable();
        }

        // If user has from() but no select(), assume all columns
        if ($this->query->hasFrom() && !$this->query->hasSelect()) {
            $this->query->assumeAllColumns();
        }

        // Validate we have at least the minimal required
        if (!$this->query->isClosure() && (!$this->query->hasSelect() || !$this->query->hasFrom())) {
            throw new QueryFlowException(
                'Query must have at least SELECT and FROM clauses. ' .
                'Called select(): ' . ($this->query->hasSelect() ? 'yes' : 'no') . ', ' .
                'Called from(): ' . ($this->query->hasFrom() ? 'yes' : 'no'),
            );
        }
    }

    protected function buildClause(string $clause): void
    {
        match($clause) {
            'select' => $this->buildSelect(),
            'from' => $this->buildFromGroup(),
            'where' => $this->buildWhere(),
            'groupBy' => $this->buildGroupBy(),
            'having' => $this->buildHaving(),
            'orderBy' => $this->buildOrderBy(),
            'limit' => $this->buildLimit(),
            'offset' => $this->buildOffset(),
            default => null
        };
    }

    private function buildWhere(): void
    {
        $conditionsMap = $this->query->getWhereConditions();
        if (empty($conditionsMap['where'])) {
            return;
        }

        $builder = new ConditionGroupBuilder($this->query->getEntityManager());

        foreach ($conditionsMap['where'] as $index => $conditionData) {
            $builder->addCondition(
                $conditionData['method'],
                (array) $conditionData['conditions'],
            );
        }

        $groupedElements = $builder->getGroupedElements();

        if ($groupedElements->isEmpty()) {
            return;
        }

        foreach ($groupedElements->all() as $element) {
            $this->query->add($element);
        }
    }

    private function buildHaving(): void
    {
        $conditionsMap = $this->query->getHavingConditions();
        $builder = new ConditionGroupBuilder($this->query->getEntityManager());

        foreach ($conditionsMap['having'] ?? [] as $conditionData) {
            $builder->addCondition(
                $conditionData['method'],
                $conditionData['conditions'],
            );
        }

        $groups = $builder->getGroupedElements();

        if ($groups->isEmpty()) {
            return;
        }

        $havingGroup = new ConditionGroup(SqlClause::HAVING);

        foreach ($groups->all() as $index => $element) {
            if ($index === 0) {
                $element instanceof ConditionGroup || $element instanceof ConditionClause ? $element->setLogicalLink(null) : '';
            }
            $havingGroup->add($element);
        }

        $this->query->add($havingGroup);
    }

    private function isJoinMethod(string $method): bool
    {
        $joinMethods = [];
        foreach (SqlJoinType::cases() as $joinType) {
            $baseName = strtolower($joinType->name);
            $joinMethods[] = $baseName . 'Join';
            $joinMethods[] = 'inner' . $baseName . 'Join';
            $joinMethods[] = $baseName;
        }

        return in_array($method, $joinMethods);
    }

    private function getUsedJoinTypes(array $userFlow): array
    {
        $joinTypes = [];
        foreach ($userFlow as $clause) {
            if ($this->isJoinMethod($clause)) {
                $baseType = str_replace(['Join', 'inner'], '', $clause);
                $joinTypes[] = ucfirst($baseType) . ' JOIN';
            }
        }
        return $joinTypes;
    }

    private function buildSelect(): void
    {
        $selectClause = new SelectClause(
            columnsCollector:$this->query->getColumnCollector(),
            em:$this->query->getEntityManager(),
            clauseContext: $this->query->getContext(),
        );

        $this->query->add($selectClause);
    }

    private function buildFromGroup(): void
    {
        $fromGroup = new FromGroup();
        $selectMap = $this->query->getSelectMap();
        if (isset($selectMap['select']['data'])) {
            $data = $selectMap['select']['data'];
        }

        // 1. Add main FROM clause (existing code)
        $from = new FromClause(
            $this->query->getFromTable(),
            isset($data) ? $data : null,
            $this->query->getEntityManager(),
            $selectMap['select']['method'] ?? null,
            $selectMap['select']['customAlias'] ?? null,
        );
        $from->setMethod(SqlClause::FROM->value);
        $fromGroup->add($from);

        // 2. Add all JOIN clauses (existing code moved here)
        foreach ($this->query->getJoinMap() as $joinKey => $joinConfig) {
            $joinClause = $this->createJoinClause($joinKey, $joinConfig);
            $fromGroup->add($joinClause);
        }

        $this->query->add($fromGroup);
    }

    private function createJoinClause(string $joinKey, array $joinConfig): JoinClause
    {
        $joinType = JoinMethod::getJoinTypeFromMethod($joinConfig['method']);

        if ($joinType === null) {
            throw new QueryFlowException("Invalid join type in key: {$joinKey}");
        }

        $tableName = is_string($joinConfig['table']) ? $joinConfig['table'] : $joinKey;

        $join = new JoinClause(
            $joinConfig['customAlias'],
            $joinConfig['table'],
            $joinConfig['withAlias'],
            null,
            $this->query->getEntityManager(),
            $joinConfig['method'] ?? null,
        );
        $join->setMethod($joinType->name)->setJoinContext($tableName);

        if ($this->query->hasOnConditionsForTable($tableName)) {
            $onClause = $this->createOnClause($tableName);
            $join->add($onClause);
        }

        return $join;
    }

    private function createOnClause(string $tableName): ConditionClause
    {
        $onData = $this->query->getOnConditionsForTable($tableName);
        $onClause = new ConditionClause(
            $onData,
            'on',
            $this->query->getEntityManager(),
        );
        $onClause->setJoinContext($onData['joinContext']);
        return $onClause;
    }

    private function buildGroupBy(): void
    {
        $groupByClause = new GroupByClause(
            $this->query->getEntityManager(),
            $this->query->getGroupByMap(),
            $this->query->getFromTable(),
        );
        $this->query->add($groupByClause);
    }

    private function buildOrderBy(): void
    {
        // Implementation for ORDER BY
        $orderByClause = new OrderByClause(
            $this->query->getOrderByColumns(),
            $this->query->getFromTable(),
        );
        $this->query->add($orderByClause);
    }

    private function buildLimit(): void
    {
        $limitClause = new LimitClause($this->query->getEntityManager(), $this->query->getLimitMap());
        $this->query->add($limitClause);
    }

    private function buildOffset(): void
    {
        $OffsetClause = new OffsetClause($this->query->getEntityManager(), $this->query->getOffsetMap());
        $this->query->add($OffsetClause);
    }

    private function normalizeClause(string $clause): string
    {
        $category = SqlMethodCategory::getCategoryForMethod($clause);
        if ($category === null) {
            return $clause;
        }
        if ($category === SqlMethodCategory::FROM && $this->isJoinMethod($clause)) {
            return 'join';
        }
        if (in_array($clause, ['on', 'andOn', 'orOn', 'onClosure'])) {
            return 'on';
        }

        return $category->toSqlClause()->value;
    }

    private function validateJoinOnPairs(): void
    {
        $userFlow = array_keys($this->query->getQueryFlow());

        $joinMap = $this->query->getJoinMap();
        $onConditions = $this->query->getOnConditions();

        // Check if user has any JOIN without ON
        $hasAnyJoin = $this->hasAnyJoin($userFlow);
        $hasOn = in_array('on', $userFlow);

        if ($hasAnyJoin && !$hasOn) {
            $joinTypes = $this->getUsedJoinTypes($userFlow);
            throw new QueryFlowException(
                'JOIN clauses require corresponding ON conditions. ' .
                'Found ' . count($joinMap) . ' JOIN(s) (' . implode(', ', $joinTypes) . ') but no ON conditions. ' .
                'Use ->on() method after each ->join() method.',
            );
        }

        // Check if user has ON without any JOIN
        if ($hasOn && !$hasAnyJoin) {
            throw new QueryFlowException(
                'ON conditions require corresponding JOIN clauses. ' .
                'Found ON conditions but no JOIN clauses. ' .
                'Use ->join() method before ->on() method.',
            );
        }

        // Validate JOIN appears before ON in the flow (using normalized clauses)
        $normalizedFlow = array_map(fn ($clause) => $this->normalizeClause($clause), $userFlow);
        $joinIndex = array_search('join', $normalizedFlow);
        $onIndex = array_search('on', $normalizedFlow);

        if ($hasAnyJoin && $hasOn && $onIndex < $joinIndex) {
            throw new QueryFlowException(
                'ON clause cannot appear before JOIN clause. ' .
                'Correct order: ->join() then ->on(). ' .
                $this->query->getFlowDiagnostics(),
            );
        }

        // Validate specific JOIN-ON table pairs
        foreach ($joinMap as $joinKey => $joinConfig) {
            $tableName = is_string($joinConfig['table']) ? $joinConfig['table'] : $joinKey;
            if (!isset($onConditions[$tableName])) {
                $joinType = explode('|', $joinKey)[0] ?? 'join';
                throw new QueryFlowException(
                    "{$joinType} clause for table '{$tableName}' requires a corresponding ON condition. " .
                    "Use ->on() method after ->{$joinType}() method.",
                );
            }
        }

        foreach ($onConditions as $tableName => $onCondition) {
            $hasMatchingJoin = false;
            foreach ($joinMap as $joinKey => $joinConfig) {
                $joinTableName = is_string($joinConfig['table']) ? $joinConfig['table'] : $joinKey;
                if ($joinTableName === $tableName || str_contains($joinKey, $tableName)) {
                    $hasMatchingJoin = true;
                    break;
                }
            }

            if (!$hasMatchingJoin) {
                throw new QueryFlowException(
                    "ON condition for table '{$tableName}' has no corresponding JOIN clause. " .
                    'Use ->join() method before ->on() method.',
                );
            }
        }
    }

    private function hasAnyJoin(array $userFlow): bool
    {
        foreach ($userFlow as $clause) {
            $category = SqlMethodCategory::getCategoryForMethod($clause);
            if ($category === SqlMethodCategory::FROM && $this->isJoinMethod($clause)) {
                return true;
            }
        }
        return false;
    }
}