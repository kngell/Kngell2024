<?php

declare(strict_types=1);

class WhereConditionRuleOLD extends AbstractConditionRules
{
    private const string PARAM_SUFFIX = 'azertyuiopmlkjhgfdsq';

    /**
     * @param EntityManagerInterface $em
     * @param QueryBuilder $builder
     * @param array $bind_arr
     * @param array $tableAlias
     * @param array $aliasCheck
     * @param array $parameters
     * @param array $tables
     * @param string $method
     */
    public function __construct(
        EntityManagerInterface $em,
        QueryBuilder $builder,
        array $bind_arr,
        array $tableAlias,
        array $aliasCheck,
        array $parameters,
        array $tables,
        string $method,
        private TypeNormalizerInterface $normalizer,
        private ?string $joinContext,
        private ?string $table,
        private array $logicalToPhysicalMap,
    ) {
        $this->em = $em;
        $this->builder = $builder;
        $this->bind_arr = $bind_arr;
        $this->tableAlias = $tableAlias;
        $this->aliasCheck = $aliasCheck;
        $this->parameters = $parameters;
        $this->tables = $tables;
        $this->method = $method;
    }

    public function getRule(array $conditions): string
    {
        $parts = [];
        $normalizedConditions = $this->normalize($conditions);

        foreach ($normalizedConditions as $index => $condition) {
            if ($condition instanceof Closure) {
                continue;
            }

            $parts[] = $this->getConditionLink($index);
            $parts[] = $this->getOpenBracket($normalizedConditions);
            $parts[] = $this->buildCondition($condition);
            $parts[] = $this->getCloseBracket($normalizedConditions);
        }

        return implode('', $parts);
    }

    protected function normalize(array $conditions): array
    {
        $newConditions = [];
        $conditions = ArrayUtils::fromAssocToSequential($conditions);
        $operator = $this->getOperation($conditions);

        $newConditions[] = [
            'left' => $conditions[0],
            'right' => $conditions[1],
            'operator' => empty($operator) ? ' = ' : $operator,
        ];

        // Process remaining conditions recursively
        unset($conditions[0], $conditions[1]);
        $remainingConditions = array_values($conditions);

        if (!empty($remainingConditions)) {
            $newConditions = array_merge($newConditions, $this->normalize($remainingConditions));
        }

        return $newConditions;
    }

    /**
     * Builds a single condition expression.
     */
    private function buildCondition(array $condition): string
    {
        $keyColumns = $this->extractKeyColumns($condition);
        $tableHelper = $this->em->getTableAliasHelper()
            ->setTables($this->tables)
            ->setConditionIndex($keyColumns);

        list($table, $column) = $tableHelper->mapTableColumn($condition['left']);
        list($table, $alias) = $tableHelper->get($table, $this->tableAlias, $this->aliasCheck);

        $leftSide = !empty($alias) ? $alias . '.' . $column : $column;
        $rightSide = $this->buildRightSide($condition, $tableHelper);

        return $leftSide . ' ' . $condition['operator'] . ' ' . $rightSide;
    }

    /**
     * Builds the right side of the condition.
     */
    private function buildRightSide(array $condition, TablesAliasHelper $tableHelper): string
    {
        if ($this->isWhereOrHavingMethod()) {
            return $this->buildParameterValue($condition, $tableHelper);
        }

        return $this->buildColumnReference($condition['right'], $tableHelper);
    }

    private function buildParameterValue(array $condition, TablesAliasHelper $tableHelper): string
    {
        $operator = Operator::from($condition['operator']);

        if (in_array($operator, [Operator::IS, Operator::IS_NOT])) {
            return $tableHelper->extractColumnName($condition['right']);
        }

        $rawValue = $condition['right'];
        $dbFieldName = $tableHelper->extractColumnName($condition['left'], $tableHelper);
        $entity = $this->em->getEntity();

        $normalizedValue = $this->normalizer->normalizeValueForDatabase(
            $dbFieldName,
            $rawValue,
            $entity,
        );

        $parameterName = $tableHelper->generateUniqueParameterName(
            $condition['left'],
            $this->parameters,
        );

        $this->parameters[$parameterName] = $normalizedValue;

        return ':' . $parameterName;
    }

    /**
     * Builds column reference for JOIN conditions.
     */
    private function buildColumnReference(string $rightCondition, TablesAliasHelper $tableHelper): string
    {
        list($table, $column) = $tableHelper->mapTableColumn($rightCondition);
        list($table, $alias) = $tableHelper->get($table, $this->tableAlias, $this->aliasCheck);

        return !empty($alias) ? $alias . '.' . $column : $column;
    }

    /**
     * Checks if current method is WHERE or HAVING.
     */
    private function isWhereOrHavingMethod(): bool
    {
        return Statement::exists($this->method) &&
               in_array($this->method, array_merge(
                   Statement::getFamily('where'),
                   Statement::getFamily('having'),
               ));
    }

    /**
     * Gets the linking operator between conditions.
     */
    private function getConditionLink(int $currentIndex): string
    {
        return $currentIndex === 0 ? '' : ' AND ';
    }

    /**
     * Gets opening bracket if needed.
     */
    private function getOpenBracket(array $conditions): string
    {
        return count($conditions) > 1 ? '(' : '';
    }

    /**
     * Gets closing bracket if needed.
     */
    private function getCloseBracket(array $conditions): string
    {
        return count($conditions) > 1 ? ')' : '';
    }

    /**
     * Extracts key columns from condition.
     */
    private function extractKeyColumns(array $condition): array
    {
        $keyColumns = $condition;
        if (array_key_last($condition) === 'operator') {
            unset($keyColumns['operator']);
        }
        return array_values($keyColumns);
    }
}