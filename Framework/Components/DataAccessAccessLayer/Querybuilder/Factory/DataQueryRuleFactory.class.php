<?php

declare(strict_types=1);

class DataQueryRuleFactory extends AbstractRulesFactory implements RuleFactoryInterface
{
    public function supports(SqlStatementType $statement): bool
    {
        return $statement === SqlStatementType::SELECT;
    }

    public function create(string $method, mixed $data): QueryRulesInterface
    {
        $methodKey = strtolower($method);

        // First, check if it's an ON method using the registry
        if ($this->isOnMethod($methodKey)) {
            return $this->createOnRules($method, $data);
        }

        // Use the registry to get clause context
        if (SqlBuilderMethodRegistry::isValidMethod($methodKey)) {
            $clause = SqlBuilderMethodRegistry::getClauseContext($methodKey);
            return $this->createFromClause($clause, $method, $data);
        }

        // Fallback for methods not in registry
        return $this->createFromMethodName($method, $data);
    }

    protected function initialize(QueryRulesInterface $rule): QueryRulesInterface
    {
        if (method_exists($rule, 'initialize')) {
            $rule->initialize($this->state);
        }
        return $rule;
    }

    /**
     * Check if method is an ON clause method.
     */
    private function isOnMethod(string $method): bool
    {
        // Check registry first
        if (SqlBuilderMethodRegistry::isValidMethod($method)) {
            $clause = SqlBuilderMethodRegistry::getClauseContext($method);
            $link = SqlBuilderMethodRegistry::getLogicalLink($method);

            // ON methods have SqlClause::FROM with SqlConditionLink::ON
            return $clause === SqlClause::FROM &&
                   $link === SqlConditionLink::ON;
        }

        // Also check common ON method patterns
        return str_starts_with($method, 'on') ||
               str_ends_with($method, 'on') ||
               in_array($method, ['on', 'andon', 'oron', 'onclause']);
    }

    /**
     * Create OnRules for ON clause methods.
     */
    private function createOnRules(string $method, mixed $data): QueryRulesInterface
    {
        return $this->initialize(new OnRules(
            $data,
            $this->em,
            $method,
            $this->state,
        ));
    }

    private function createGroupByRules(string $method, mixed $data): QueryRulesInterface
    {
        return $this->initialize(new GroupByRule(
            $data,
            $this->em,
            $method,
            $this->state,
        ));
    }

    /**
     * Create rule based on SQL clause.
     */
    private function createFromClause(SqlClause $clause, string $method, mixed $data): QueryRulesInterface
    {
        return match($clause) {
            SqlClause::WHERE,
            SqlClause::HAVING => $this->initialize(new WhereRules(
                $data,
                $this->em,
                $method,
                $this->state,
            )),

            SqlClause::LIMIT => $this->initialize(new LimitRule(
                $data,
                $this->em,
                $method,
                $this->state,
            )),

            SqlClause::OFFSET => $this->initialize(new OffsetRule(
                $data,
                $this->em,
                $method,
                $this->state,
            )),

            SqlClause::ORDER_BY => $this->initialize(new OrderByRule(
                $data,
                $this->em,
                $method,
                $this->state,
            )),

            SqlClause::GROUP_BY => $this->initialize(new GroupByRule(
                $data,
                $this->em,
                $method,
                $this->state,
            )),

            // ON methods map to SqlClause::FROM but use OnRules
            SqlClause::FROM => $this->handleFromClause($method, $data),

            // Other clauses that don't need rules
            SqlClause::SELECT,
            SqlClause::INTO,
            SqlClause::VALUES => throw new LogicException(
                "Clause '{$clause->name}' does not require a condition rule",
            ),

            default => throw new InvalidArgumentException(
                'No rule implemented for SQL clause: ' . $clause->name,
            ),
        };
    }

    /**
     * Handle FROM clause - could be ON or other FROM operations.
     */
    private function handleFromClause(string $method, mixed $data): QueryRulesInterface
    {
        $methodKey = strtolower($method);

        // Check if this is an ON method
        if (SqlBuilderMethodRegistry::isValidMethod($methodKey)) {
            $link = SqlBuilderMethodRegistry::getLogicalLink($methodKey);

            if ($link === SqlConditionLink::ON) {
                return $this->createOnRules($method, $data);
            }
        }

        // Default for other FROM operations (e.g., table aliases)
        // These might not need rules, or need different rules
        throw new LogicException(
            "FROM clause operation '{$method}' does not have a rule implementation",
        );
    }

    /**
     * Fallback: create rule based on method name pattern.
     */
    private function createFromMethodName(string $method, mixed $data): QueryRulesInterface
    {
        $methodKey = strtolower($method);

        // Check common patterns
        if (str_starts_with($methodKey, 'where')) {
            return $this->initialize(new WhereRules(
                $data,
                $this->em,
                $method,
                $this->state,
            ));
        }

        if (str_starts_with($methodKey, 'having')) {
            return $this->initialize(new WhereRules( // HAVING uses WhereRules
                $data,
                $this->em,
                $method,
                $this->state,
            ));
        }

        if (str_starts_with($methodKey, 'on')) {
            return $this->createOnRules($method, $data);
        }
        if (str_starts_with($methodKey, 'groupby')) {
            return $this->createGroupByRules($method, $data);
        }

        if ($methodKey === 'limit') {
            return $this->initialize(new LimitRule(
                $data,
                $this->em,
                $method,
                $this->state,
            ));
        }

        if ($methodKey === 'offset') {
            return $this->initialize(new OffsetRule(
                $data,
                $this->em,
                $method,
                $this->state,
            ));
        }

        throw new InvalidArgumentException(
            "Unknown method '{$method}'. No rule mapping found.",
        );
    }
}