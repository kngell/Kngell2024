<?php

declare(strict_types=1);

class ClosureConditionRule extends AbstractConditionRules
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
        private mixed $conditions,
    ) {
        $this->em = $em;
        $this->tables = $tables;
        $this->method = $method;
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
            bindArr: $this->bindArr,
            logicalToPhysicalMap: $this->logicalToPhysicalMap,
            tables: $this->tables, // 🔥 CRITICAL: Pass tables for initialization
            joinContext: null,
            withAlias: false,
            customAlias: null,
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
        // For non-closure conditions, use the existing WhereRules
        $whereRules = new WhereRules($this->method, [$condition], $this->em, $this->tables);

        // Transfer state from the WhereRules to this closure rule
        $this->mergeWhereRulesState($whereRules);

        return $whereRules->getRule([$condition]);
    }

    private function mergeNestedStateSafely(SqlSelectQuery $nestedQuery): void
    {
        $nestedState = $nestedQuery->getState();

        // Merge table aliases
        $this->tableAlias = array_merge($this->tableAlias, $nestedState->tableAlias);
        $this->aliasCheck = array_merge($this->aliasCheck, $nestedState->aliasCheck);
        $this->parameters = array_merge($this->parameters, $nestedState->parameters);
        $this->bindArr = array_merge($this->bindArr, $nestedState->bindArr);
        $this->logicalToPhysicalMap = array_merge($this->logicalToPhysicalMap, $nestedState->logicalToPhysicalMap);
    }

    private function mergeWhereRulesState(WhereRules $whereRules): void
    {
        $this->tableAlias = array_merge($this->tableAlias, $whereRules->getTableAlias());
        $this->aliasCheck = array_merge($this->aliasCheck, $whereRules->getAliasCheck());
        $this->parameters = array_merge($this->parameters, $whereRules->getParameters());
        $this->bindArr = array_merge($this->bindArr, $whereRules->getBindArr());
    }
}