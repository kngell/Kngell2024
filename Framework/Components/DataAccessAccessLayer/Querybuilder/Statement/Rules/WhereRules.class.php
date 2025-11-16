<?php

declare(strict_types=1);

class WhereRules extends AbstractRules implements QueryRulesInterface
{
    public function __construct(
        private mixed $conditions,
        EntityManagerInterface $em,
        string $method,
        QueryState $state,
    ) {
        parent::__construct($em, $method, $state);
    }

    public function getRule(array $conditions): string
    {
        $parts = [];
        $normalizedConditions = $this->normalize($conditions);
        foreach ($normalizedConditions as $index => $condition) {
            // 🔥 Handle closures by delegating to ClosureConditionRule
            if ($condition instanceof Closure) {
                $closureRule = new ClosureConditionRule($this->em, $this->tables, $this->method, $condition);
                return $closureRule->getRule([$condition]);
            }

            // Handle regular conditions
            $parts[] = $this->getConditionLink($index);
            // $parts[] = $this->getOpenBracket($normalizedConditions);
            $parts[] = $this->buildCondition($condition);
            // $parts[] = $this->getCloseBracket($normalizedConditions);
        }

        if (!empty($parts) && empty(trim($parts[0]))) {
            array_shift($parts);
        }

        return implode('', $parts);
    }

    /**
     * @return mixed
     */
    public function getConditions(): mixed
    {
        return $this->conditions;
    }

    protected function normalize(array $conditions): array
    {
        $newConditions = [];

        // Handle mixed conditions (closures + regular)
        foreach ($conditions as $condition) {
            if ($condition instanceof Closure) {
                $newConditions[] = $condition;
                continue;
            }

            if (ArrayUtils::isAssoc($conditions)) {
                return $this->normalizeAssociative($conditions);
            }

            $conditions = ArrayUtils::fromAssocToSequential($conditions);
            $operator = $this->getOperation($conditions);

            $newConditions[] = [
                'left' => $conditions[0],
                'right' => $conditions[1],
                'operator' => empty($operator) ? ' = ' : $operator,
            ];

            unset($conditions[0], $conditions[1]);
            $remainingConditions = array_values($conditions);

            if (!empty($remainingConditions)) {
                $newConditions = array_merge($newConditions, $this->normalize($remainingConditions));
            }

            break; // Break after processing non-closure conditions
        }

        return $newConditions;
    }

    private function normalizeAssociative(array $conditions): array
    {
        $newConditions = [];

        foreach ($conditions as $left => $right) {
            $newConditions[] = [
                'left' => $left,
                'right' => $right,
                'operator' => ' = ',
            ];
        }

        return $newConditions;
    }

    private function buildCondition(array $condition): string
    {
        $tableHelper = $this->createTableHelper();
        $tableAlias = $this->state->tableAlias;
        $aliasCheck = $this->state->aliasCheck;

        list($table, $column) = $tableHelper->mapTableColumn($condition['left']);

        list($table, $alias) = $tableHelper->get($table, $tableAlias, $aliasCheck);

        // Update state...
        $leftSide = !empty($alias) ? $alias . '.' . $column : $column;
        $rightSide = $this->buildRightSide($condition, $tableHelper);

        return $leftSide . ' ' . $condition['operator'] . ' ' . $rightSide;
    }

    private function buildRightSide(array $condition, TablesAliasHelper $tableHelper): string
    {
        $rawValue = $condition['right'];

        if ($rawValue instanceof SqlQuery) {
            $this->prepareSubQueryState($rawValue);
            $rawString = $rawValue->build();
            $this->mergeSubQueryState($rawValue);

            return '(' . $rawString . ')';
        }

        if ($this->isWhereOrHavingMethod()) {
            return $this->buildParameterValue($condition, $tableHelper);
        }

        return $this->buildColumnReference($condition['right'], $tableHelper);
    }

    private function buildParameterValue(array $condition, TablesAliasHelper $tableHelper): string
    {
        $operator = SqlOperator::tryFrom($condition['operator']);
        $rawValue = $condition['right'];

        if (in_array($operator, [SqlOperator::IS_NULL, SqlOperator::IS_NOT_NULL])) {
            return $tableHelper->extractColumnName($rawValue);
        }

        // Handle arrays for IN conditions
        if ($this->isInMethod() && is_array($rawValue)) {
            return $this->buildParameterList($rawValue, $condition, $tableHelper);
        }

        // Handle single parameter value
        return $this->buildSingleParameter($rawValue, $condition, $tableHelper);
    }

    private function buildParameterList(array $values, array $condition, TablesAliasHelper $tableHelper): string
    {
        $parameterNames = [];
        $entity = $this->em->getEntity();
        foreach ($values as $index => $value) {
            $parameterName = $this->createParameter($value, $condition['left'], $tableHelper, $index, $entity);
            $parameterNames[] = ':' . $parameterName;
        }

        return '(' . implode(', ', $parameterNames) . ')';
    }

    private function buildSingleParameter(mixed $value, array $condition, TablesAliasHelper $tableHelper): string
    {
        $entity = $this->em->getEntity();
        $parameterName = $this->createParameter($value, $condition['left'], $tableHelper, null, $entity);

        return ':' . $parameterName;
    }

    private function isInMethod(): bool
    {
        $inMethods = array_merge(SqlBuilderMethodRegistry::getMethodsForOperator(SqlOperator::IN), SqlBuilderMethodRegistry::getMethodsForOperator(SqlOperator::NOT_IN));

        return in_array($this->method, $inMethods);
    }

    private function buildColumnReference(string $rightCondition, TablesAliasHelper $tableHelper): string
    {
        list($table, $column) = $tableHelper->mapTableColumn($rightCondition);

        $tableAlias = $this->state->tableAlias;
        $aliasCheck = $this->state->aliasCheck;

        list($table, $alias) = $tableHelper->get($table, $tableAlias, $aliasCheck);

        $this->state->tableAlias = $tableAlias;
        $this->state->aliasCheck = $aliasCheck;

        return !empty($alias) ? $alias . '.' . $column : $column;
    }

    private function createTableHelper(): TablesAliasHelper
    {
        $keyColumns = $this->extractKeyColumns();

        return $this->em->getTableAliasHelper()
            ->setTables($this->tables)
            ->setConditionIndex($keyColumns);
    }

    private function extractKeyColumns(): array
    {
        $keyColumns = is_array($this->conditions) ? $this->conditions : [$this->conditions];
        return array_filter($keyColumns, fn ($item) => !$item instanceof Closure);
    }

    private function isWhereOrHavingMethod(): bool
    {
        $whereMethods = SqlBuilderMethodRegistry::getMethodsForClause(SqlClause::WHERE);
        $havingMethods = SqlBuilderMethodRegistry::getMethodsForClause(SqlClause::HAVING);
        return in_array($this->method, array_merge($whereMethods, $havingMethods));
    }

    private function getOpenBracket(array $conditions): string
    {
        // Don't add brackets for closures - they handle their own grouping
        $nonClosureConditions = array_filter($conditions, fn ($c) => !$c instanceof Closure);
        return count($nonClosureConditions) > 1 ? '(' : '';
    }

    private function getCloseBracket(array $conditions): string
    {
        // Don't add brackets for closures - they handle their own grouping
        $nonClosureConditions = array_filter($conditions, fn ($c) => !$c instanceof Closure);
        return count($nonClosureConditions) > 1 ? ')' : '';
    }
}