<?php

declare(strict_types=1);

class DataQueryRuleFactory extends AbstractRulesFactory implements RuleFactoryInterface
{
    public function supports(SqlStatement $statement): bool
    {
        return $statement === SqlStatement::SELECT;
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

    private function createOnRules(string $method, mixed $data): QueryRulesInterface
    {
        $context = $this->state->statementContext;

        return match(true) {
            // Bulk update scenarios - use bulk row factory
            in_array($method, ['FROM', 'INNER']) && ($context === StatementType::BULK_UPDATE_MARIADB ||
             $context === StatementType::BULK_UPDATE) =>
                 $this->createBulkRowStrategy($method, $data),

            // Regular ON clauses
            default => $this->initialize(new OnRule(
                $data,
                $this->em,
                $method,
                $this->state,
                new ConditionNormalizer(),
            ))
        };
    }

    private function createBulkRowStrategy(string $method, mixed $data): QueryRulesInterface
    {
        $strategyType = $this->getStrategyTypeForContext();

        $bulkRow = $this->bulkRowFactory->create(
            $this->em,
            $method,
            $this->state,
            $data,
            $strategyType,
        );

        return $this->initialize($bulkRow);
    }

    private function getStrategyTypeForContext(): ?string
    {
        $context = $this->state->statementContext;

        if ($context === StatementType::BULK_UPDATE) {
            return 'unionAll';
        }

        return null;
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
            SqlClause::HAVING => $this->initialize(new WhereRule(
                $data,
                $this->em,
                $method,
                $this->state,
                new ConditionNormalizer(),
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

    private function handleFromClause(string $method, mixed $data): QueryRulesInterface
    {
        $methodKey = strtolower($method);
        // $context = $this->state->statementContext;

        // Check if this is a bulk update context
        // if ($context === StatementType::BULK_UPDATE || $context === StatementType::BULK_UPDATE_MARIADB) {
        //     return $this->createBulkRowStrategy($method, $data);
        // }

        // Check if this is an ON method
        if (SqlBuilderMethodRegistry::isValidMethod($methodKey)) {
            $link = SqlBuilderMethodRegistry::getLogicalLink($methodKey);

            if ($link === SqlConditionLink::ON) {
                return $this->createOnRules($method, $data);
            }
        }

        // Default for other FROM operations
        throw new LogicException(
            "FROM clause operation '{$method}' does not have a rule implementation",
        );
    }

    // private function handleFromClause(string $method, mixed $data): QueryRulesInterface
    // {
    //     $methodKey = strtolower($method);

    //     if (SqlBuilderMethodRegistry::isValidMethod($methodKey)) {
    //         $link = SqlBuilderMethodRegistry::getLogicalLink($methodKey);

    //         if ($link === SqlConditionLink::ON) {
    //             return $this->createOnRules($method, $data);
    //         }
    //     }

    //     throw new LogicException(
    //         "FROM clause operation '{$method}' does not have a rule implementation",
    //     );
    // }

    private function createFromMethodName(string $method, mixed $data): QueryRulesInterface
    {
        $methodKey = strtolower($method);

        if (str_contains($methodKey, 'where')) {
            return $this->initialize(new WhereRule(
                $data,
                $this->em,
                $method,
                $this->state,
                new ConditionNormalizer(),
            ));
        }

        if (str_starts_with($methodKey, 'having')) {
            return $this->initialize(new WhereRule( // HAVING uses WhereRule
                $data,
                $this->em,
                $method,
                $this->state,
                new ConditionNormalizer(),
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
