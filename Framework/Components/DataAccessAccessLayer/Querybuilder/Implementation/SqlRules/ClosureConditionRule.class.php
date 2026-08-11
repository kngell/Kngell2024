<?php

declare(strict_types=1);

class ClosureConditionRule extends AbstractRules
{
    protected array $tableAlias = [];
    protected array $aliasCheck = [];
    protected array $parameters = [];
    protected array $bindArr = [];
    protected array $logicalToPhysicalMap = [];

    public function __construct(
        EntityManagerInterface $em,
        array $tables,
        string $method,
        private QueryState $state,
        private mixed $conditions,
        ?string $customAlias = null,
    ) {
        parent::__construct($em, $method, $customAlias, $state);
        $this->tables = $tables;
    }

    public function getRule(array $conditions): string
    {
        $rules = [];

        foreach ($conditions as $condition) {
            if ($condition instanceof Closure) {
                $rules[] = $this->processClosure($condition);
            } else {
                // Handle non-closure conditions normally
                $rules[] = $this->processRegularCondition($condition);
            }
        }

        return implode(' ', array_filter($rules));
    }

    public function getConditions(): mixed
    {
        return $this->conditions;
    }

    public function getBindArr(): array
    {
        return $this->bindArr;
    }

    public function getTableAlias(): array
    {
        return $this->tableAlias;
    }

    public function getAliasCheck(): array
    {
        return $this->aliasCheck;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    protected function normalize(array $arrayInput): array
    {
        return $arrayInput;
    }

    private function processClosure(Closure $closure): string
    {
        $nestedQuery = new SqlSelectQuery($this->em);

        // Ensure tables are available for state initialization
        $nestedState = new QueryState(
            tableAlias: $this->tableAlias,
            aliasCheck: $this->aliasCheck,
            parameters: $this->parameters,
            logicalToPhysicalMap: $this->logicalToPhysicalMap,
            tables: $this->tables, // 🔥 CRITICAL: Pass tables for initialization
            joinContext: null,
            withAlias: false,
        );

        // Initialize BEFORE using the query
        $nestedQuery->initializeWithDependencies($this->em->getTableAliasHelper(), $nestedState);

        $closure($nestedQuery);

        // Build and extract condition content
        $nestedSql = $this->buildNestedQuery($nestedQuery);

        $this->mergeNestedStateSafely($nestedQuery);

        return $nestedSql;
    }

    private function buildNestedQuery(SqlSelectQuery $nestedQuery): string
    {
        // Build the full query
        $fullSql = $nestedQuery->setClosureState()->build();

        // Extract just the condition CONTENT (remove WHERE/HAVING keywords)
        return $this->extractConditionContentFromSql($fullSql);
    }

    private function extractConditionContentFromSql(string $fullSql): string
    {
        // Look for WHERE or HAVING in the SQL and extract everything after it
        if (preg_match('/\b(WHERE|HAVING)\b(.*)/i', $fullSql, $matches)) {
            $conditionContent = trim($matches[2]);

            // Remove any trailing clauses like ORDER BY, LIMIT if they exist
            $conditionContent = preg_replace('/\s*(ORDER BY|LIMIT|OFFSET).*$/i', '', $conditionContent);

            // 🔥 CRITICAL: Return just the condition content, not the WHERE keyword
            return !empty($conditionContent) ? "({$conditionContent})" : '';
        }

        return '';
    }

    private function processRegularCondition($condition): string
    {
        // For non-closure conditions, use the existing WhereRule
        $WhereRule = new WhereRule([$condition], $this->em, $this->method, $this->state, new ConditionNormalizer());

        // Transfer state from the WhereRule to this closure rule
        $this->mergeWhereRuleState();

        return $WhereRule->getRule([$condition]);
    }

    private function mergeNestedStateSafely(SqlSelectQuery $nestedQuery): void
    {
        $nestedState = $nestedQuery->getState();

        // Merge table aliases
        $this->tableAlias = array_merge($this->tableAlias, $nestedState->tableAlias);
        $this->aliasCheck = array_merge($this->aliasCheck, $nestedState->aliasCheck);
        $this->parameters = array_merge($this->parameters, $nestedState->parameters);
        $this->logicalToPhysicalMap = array_merge($this->logicalToPhysicalMap, $nestedState->logicalToPhysicalMap);
    }

    private function mergeWhereRuleState(): void
    {
        $this->tableAlias = array_merge($this->tableAlias, $this->state->tableAlias);
        $this->aliasCheck = array_merge($this->aliasCheck, $this->state->aliasCheck);
        $this->parameters = array_merge($this->parameters, $this->state->parameters);
    }
}