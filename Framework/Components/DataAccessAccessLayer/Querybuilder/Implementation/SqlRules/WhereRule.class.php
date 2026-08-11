<?php

declare(strict_types=1);

class WhereRule extends AbstractRules
{
    public function __construct(
        private mixed $conditions,
        EntityManagerInterface $em,
        string $method,
        QueryState $state,
        private ConditionNormalizer $conditionNormalizer,
        ?string $customAlias = null,
    ) {
        parent::__construct($em, $method, $customAlias, $state);
    }

    public function getRule(array $conditions): string
    {
        $parts = [];
        $normalizedConditions = $this->normalize($conditions);
        $keyColumns = $this->extractKeyColumns();
        $tableHelper = $this->createTableHelper($keyColumns);
        foreach ($normalizedConditions as $index => $condition) {
            $link = $this->getConditionLink($index);

            if ($condition instanceof Closure) {
                $closureRule = new ClosureConditionRule(
                    $this->em,
                    $this->tables,
                    $this->method,
                    $this->state,
                    $condition,
                );

                $parts[] = $link . $closureRule->getRule([$condition]);
                continue; // Move to the next condition in the array
            }

            if ($this->isInCondition($condition) && !str_contains(strtolower($this->method), 'in')) {
                $this->method = 'whereIn';
                $condition['operator'] = $this->getOperation($condition);
            }
            if ($this->isOnValueCondition()) {
                $this->method = 'where';
            }

            $parts[] = $link . $this->buildCondition($condition, $tableHelper);
        }

        if (!empty($parts)) {
            $parts[0] = ltrim($parts[0]);
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
        $flat = $this->conditionNormalizer->normalize($conditions);

        $newConditions = [];
        $count = count($flat);
        $i = 0;

        while ($i < $count) {
            $left = $flat[$i];
            if (!is_string($left)) {
                $i++;
                continue;
            }

            $token = $flat[$i + 1] ?? null;
            $op = is_string($token) ? SqlOperator::tryFrom(strtoupper($token)) : null;

            if ($op) {
                $newConditions[] = [
                    'left' => $left,
                    'operator' => $op->value,
                    'right' => $op->isUnary() ? null : ($flat[$i + 2] ?? null),
                ];
                // Jump 2 for IS NULL, 3 for Binary (=, >, IN)
                $i += $op->isUnary() ? 2 : 3;
            } else {
                // Standard Key => Value
                $newConditions[] = [
                    'left' => $left,
                    'operator' => '=',
                    'right' => $token,
                ];
                $i += 2;
            }
        }

        return $newConditions;
    }

    protected function buildColumnReference(mixed $rightCondition, TablesAliasHelper $tableHelper): string
    {
        list($table, $column) = $tableHelper->mapTableColumn($rightCondition, 1);

        $tableAlias = $this->state->tableAlias;
        $aliasCheck = $this->state->aliasCheck;

        list($table, $alias) = $tableHelper->get($table, $tableAlias, $aliasCheck);

        $this->state->tableAlias = $tableAlias;
        $this->state->aliasCheck = $aliasCheck;

        return !empty($alias) ? $alias . '.' . $column : $column;
    }

    protected function buildRightSide(array $condition, TablesAliasHelper $tableHelper): string
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

    protected function isInCondition(array $condition): bool
    {
        return is_array($condition['right']) || str_contains(strtolower($condition['operator']), 'in');
    }

    protected function isOnValueCondition(): bool
    {
        return in_array($this->method, ['onValue', 'orOnValue']);
    }

    private function buildCondition(array $condition, TablesAliasHelper $tableHelper): string
    {
        $tableAlias = $this->state->tableAlias;
        $aliasCheck = $this->state->aliasCheck;
        $alias = $this->customAlias;
        list($table, $column) = $tableHelper->mapTableColumn($condition['left']);
        if ($alias === null) {
            list($table, $alias) = $tableHelper->get($table, $tableAlias, $aliasCheck);
        }

        if (ColumnTypeDetector::isComplexExpression($column)) {
            $runtimeAliasMap = $tableAlias;
            if (!empty($table) && !empty($alias)) {
                $runtimeAliasMap[$table] = $alias;
            }
            $fallbackKey = $table ?? array_key_first($tableAlias) ?? '';

            $parser = new SqlExpressionParser($column);
            $leftSide = $parser->parseAndBuild($fallbackKey, false, $runtimeAliasMap);
        } else {
            $leftSide = !empty($alias) ? $alias . '.' . $column : $column;
        }

        $rightSide = $this->buildRightSide($condition, $tableHelper);
        if (is_string($rightSide) && SqlOperator::exists($rightSide)) {
            return $leftSide . ' ' . $rightSide;
        }
        return $leftSide . ' ' . $condition['operator'] . ' ' . $rightSide;
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
        $entities = $this->em->getEntity();
        if ($entities instanceof CollectionInterface || (is_array($entities) && ArrayUtils::isObjectList($entities))) {
            $entity = $entities[0];
        } elseif ($entities instanceof Entity) {
            $entity = $entities;
        }
        foreach ($values as $index => $value) {
            $parameterName = $this->createParameter($value, $condition['left'], $tableHelper, $index, $entity);
            $parameterNames[] = $parameterName;
        }

        return '(' . implode(', ', $parameterNames) . ')';
    }

    private function buildSingleParameter(mixed $value, array $condition, TablesAliasHelper $tableHelper): string
    {
        if (SqlFunction::isSqlFunction($value) || (is_string($value) && SqlOperator::exists($value))) {
            return (string) $value;
        }

        $entity = $this->em->getEntity();
        if ($entity instanceof CollectionInterface || (is_array($entity) && ArrayUtils::isObjectList($entity))) {
            $entity = $entity[0];
        }

        return $this->createParameter($value, $condition['left'], $tableHelper, null, $entity);
    }

    private function isInMethod(): bool
    {
        $inMethods = array_merge(SqlBuilderMethodRegistry::getMethodsForOperator(SqlOperator::IN), SqlBuilderMethodRegistry::getMethodsForOperator(SqlOperator::NOT_IN));

        return in_array($this->method, $inMethods);
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