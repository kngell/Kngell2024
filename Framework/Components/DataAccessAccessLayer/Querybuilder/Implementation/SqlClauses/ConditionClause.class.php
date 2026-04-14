<?php

declare(strict_types=1);

class ConditionClause extends SqlQuery implements RegularClauseComponentInterface, OperatorAwareInterface
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
        parent::__construct($this->clauseContext, null, $em);
        $this->method = $method;
    }

    public function getSqlClause(): SqlClause
    {
        return $this->clauseContext;
    }

    public function build(): string
    {
        $this->initializeConditionRule();
        $conditions = $this->getConditions();
        $conditionSql = $this->conditionRule->getRule($conditions);
        if (empty($conditionSql)) {
            return '';
        }

        $childrenSql = parent::build();

        // Update state from condition rule
        if ($this->conditionRule instanceof QueryRulesInterface && method_exists($this->conditionRule, 'getState')) {
            $this->state = $this->state->merge($this->conditionRule->getState());
        }
        // if ($this->requiresGrouping()) {
        //     $conditionSql = '(' . $conditionSql . ')';
        // }
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
        if ($this->method === 'on') {
            $this->helper->setJoinMapping(
                $this->conditions['fromTable'],
                $this->conditions['toTable'],
            );
            return $this->conditions['onConditions'];
        }
        return (array) $this->conditions;
    }

    public function requiresGrouping(): bool
    {
        // $ruleMethod = $this->conditionRule->getMethod();
        // $ruleLogicalLink = SqlBuilderMethodRegistry::getLogicalLink($ruleMethod);

        // OR conditions always need grouping
        if ($this->getLogicalLink() === 'OR') {
            //|| $ruleLogicalLink->name === 'OR')
            // if ($this->getLogicalLink() === 'OR') {
            //     $this->logicalLink = SqlConditionLink::AND->name;
            // }
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

    public function getRuleReport(): array
    {
        $mapping = SqlBuilderMethodRegistry::getMapping($this->method);
        return [
            'method' => $this->method,
            'logic' => $mapping['link']->name,
            'sql_op' => $mapping['operator']->value,
            'precedence' => $mapping['operator']->getPrecedence(),
        ];
    }

    private function isComplexCondition(): bool
    {
        if ($this->conditions instanceof Closure) {
            return true;
        }

        if (is_array($this->conditions)) {
            foreach ($this->conditions as $val) {
                if (is_string($val) && in_array(strtoupper($val), ['OR', 'AND', 'new_block'])) {
                    return true;
                }
            }
        }
        return false;
    }

    private function initializeConditionRule(): void
    {
        if (!isset($this->conditionRule)) {
            if ($this->joinContext !== null) {
                $this->state->joinContext = $this->joinContext;
            }
            $registry = new SqlFactoryRegistry($this, $this->em, $this->state);
            $this->conditionRule = $registry->getRule($this->method, $this->conditions);
        }
    }

    private function extractOperatorFromConditions(): ?SqlOperator
    {
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
        $junction = $this->getLogicalLink();
        return empty($childrenSql)
             ? $conditionSql
             : "{$conditionSql} {$junction} {$childrenSql}";
        // return $this->applyConditionParentheses($conditionSql);
    }

    private function applyConditionParentheses(string $sql): string
    {
        if ($this->requiresGrouping()) {
            $sql = '(' . $sql . ')';

            if ($this->getLogicalLink() === SqlConditionLink::OR) {
                $this->logicalLink = SqlConditionLink::AND->name;
            }
        }
        return $sql;
    }
}