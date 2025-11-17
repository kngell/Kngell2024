<?php

declare(strict_types=1);

class ConditionClause extends SqlQuery implements ClauseComponentInterface, OperatorAwareInterface
{
    private ?SqlOperator $operator = null;
    private ?SqlClause $clauseContext;
    private ?QueryRulesInterface $conditionRule;

    public function __construct(
        private mixed $conditions,
        string $method,
        EntityManagerInterface $em,
    ) {
        $this->clauseContext = SqlBuilderMethodRegistry::getClauseContext($method);
        parent::__construct($this->clauseContext, $em);
        $this->method = $method;
    }

    public function getSqlClause(): SqlClause
    {
        return $this->clauseContext;
    }

    public function build(): string
    {
        $this->initializeConditionRule();

        $conditionSql = $this->conditionRule->getRule((array) $this->conditions);
        if (empty($conditionSql)) {
            return '';
        }

        $childrenSql = parent::build();

        // Update state from condition rule
        if ($this->conditionRule instanceof QueryRulesInterface && method_exists($this->conditionRule, 'getState')) {
            $this->state = $this->state->merge($this->conditionRule->getState());
        }

        return $this->combineWithChildren($conditionSql, $childrenSql);
        // return $this->applyConditionParentheses($result);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getOperator(): SqlOperator
    {
        if ($this->operator === null) {
            $this->operator = $this->extractOperatorFromConditions()
                ?? SqlBuilderMethodRegistry::getDefaultOperator($this->method);
        }
        return $this->operator;
    }

    public function setOperator(SqlOperator $operator): void
    {
        $this->operator = $operator;
    }

    public function getLogicalLink(): string
    {
        if ($this->logicalLink === null) {
            $this->logicalLink = SqlBuilderMethodRegistry::getLogicalLink($this->method)->name;
        }
        return $this->logicalLink;
    }

    public function getConditions(): array
    {
        return (array) $this->conditions;
    }

    /**
     * Check if this condition requires grouping due to complexity.
     */
    public function requiresGrouping(): bool
    {
        // OR conditions always need grouping
        if ($this->getLogicalLink() === 'OR') {
            return true;
        }

        // Low precedence operators often need grouping
        if ($this->getOperator()->getPrecedence() <= 10) {
            return true;
        }

        // Complex conditions need grouping
        if ($this->isComplexCondition()) {
            return true;
        }

        return false;
    }

    /**
     * @return null|ConditionRuleInterface
     */
    public function getRules(): ?QueryRulesInterface
    {
        if (isset($this->conditionRule)) {
            return $this->conditionRule;
        }
        return null;
    }

    /**
     * Check if this is a complex condition that might need parentheses.
     */
    private function isComplexCondition(): bool
    {
        if ($this->conditions instanceof Closure) {
            return true;
        }

        if (is_array($this->conditions) && count($this->conditions) > 2) {
            return true;
        }

        if ($this->getOperator() === SqlOperator::IN && is_array($this->conditions[2] ?? null)) {
            return count($this->conditions[2]) > 3;
        }

        // Subqueries and function calls are complex
        if (is_string($this->conditions[0] ?? null) && str_contains($this->conditions[0], '(')) {
            return true;
        }

        return false;
    }

    private function initializeConditionRule(): void
    {
        if (!isset($this->conditionRule)) {
            $registry = new FactoryRegistry($this, $this->em, $this->state);
            $this->conditionRule = $registry->getRule($this->method, $this->conditions);
            // $factory = new RulesFactory(
            //     $this->em,
            //     $this->state,
            // );

            // $this->conditionRule = $factory->create($this->method, $this->conditions);
        }
    }

    private function extractOperatorFromConditions(): ?SqlOperator
    {
        // Extract operator from conditions array structure
        // Example: ['column', '=', 'value'] or ['column', SqlOperator::EQUALS, 'value']
        if (is_array($this->conditions) && count($this->conditions) >= 2) {
            $operator = $this->conditions[1] ?? null;
            if ($operator instanceof SqlOperator) {
                return $operator;
            }
            if (is_string($operator)) {
                // Try to map string operator to SqlOperator
                return SqlOperator::tryFrom($operator);
            }
        }
        return null;
    }

    private function combineWithChildren(string $conditionSql, string $childrenSql): string
    {
        if (empty($childrenSql)) {
            return $conditionSql;
        }

        // If we have both condition and children, combine with AND
        return "{$conditionSql} AND {$childrenSql}";
    }

    private function applyConditionParentheses(string $sql): string
    {
        if ($this->requiresGrouping()) {
            return "({$sql})";
        }
        return $sql;
    }
}