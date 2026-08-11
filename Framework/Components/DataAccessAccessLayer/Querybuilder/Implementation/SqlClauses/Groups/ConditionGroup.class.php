<?php

declare(strict_types=1);

class ConditionGroup extends SqlQuery implements ClauseComponentInterface, OperatorAwareInterface, LogicalContainerInterface
{
    private array $conditions = [];
    private ?string $groupLink = null;
    private bool $isExplicit = false;

    public function __construct(private SqlClause $clauseContext)
    {
        parent::__construct($clauseContext);
    }

    public function build(): string
    {
        if ($this->children->isEmpty()) {
            return '';
        }

        $builtParts = [];

        foreach ($this->children->all() as $index => $child) {
            $this->prepareChild($child);

            $conditionSql = $child->build();
            if (empty($conditionSql)) {
                continue;
            }

            // Add logical operator if not first condition
            if ($index > 0) {
                $logicalOperator = $this->getLogicalOperatorForChild($child, $index);
                $builtParts[] = $logicalOperator;
            }

            $builtParts[] = $conditionSql;
            $this->mergeChildState($child);
        }

        $result = implode(' ', $builtParts);

        return $this->needsGroupParentheses() ? "({$result})" : $result;
    }

    public function addCondition(ConditionClause $condition): void
    {
        $this->conditions[] = $condition;
    }

    public function getSqlClause(): SqlClause
    {
        return $this->clauseContext;
    }

    public function getClauseContext(): SqlClause
    {
        return $this->clauseContext;
    }

    public function isEmpty(): bool
    {
        return empty($this->conditions);
    }

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function setGroupLink(string $link): void
    {
        $this->groupLink = $link;
    }

    public function getGroupLink(): string
    {
        return $this->groupLink ?? 'AND';
    }

    public function getOperator(): ?SqlOperator
    {
        return null;
    }

    public function getLogicalLink(): string
    {
        return $this->groupLink ?? 'AND';
    }

    public function hasOrOperators(): bool
    {
        foreach ($this->children->all() as $child) {
            if ($child instanceof ConditionClause && $child->getLogicalLink() === 'OR') {
                return true;
            }
            if ($child instanceof ConditionGroup && $child->hasOrOperators()) {
                return true;
            }
        }
        return false;
    }

    public function hasMixedOperators(): bool
    {
        $hasAnd = false;
        $hasOr = false;

        foreach ($this->conditions as $condition) {
            $link = $condition->getLogicalLink();
            if ($link === 'AND') {
                $hasAnd = true;
            }
            if ($link === 'OR') {
                $hasOr = true;
            }

            if ($hasAnd && $hasOr) {
                return true;
            }
        }

        return false;
    }

    public function getLastCondition(): ?ConditionClause
    {
        return !empty($this->conditions) ? end($this->conditions) : null;
    }

    public function getFirstCondition(): ?ConditionClause
    {
        return !empty($this->conditions) ? $this->conditions[0] : null;
    }

    /**
     * @param bool $isExplicit
     *
     * @return ConditionGroup
     */
    public function setIsExplicit(bool $isExplicit): ConditionGroup
    {
        $this->isExplicit = $isExplicit;

        return $this;
    }

    private function getLogicalOperatorForChild(ClauseComponentInterface $child, int $index): string
    {
        if ($child instanceof ConditionClause) {
            return $child->getLogicalLink();
        }

        if ($child instanceof ConditionGroup) {
            return $child->getGroupLink();
        }

        // Default to AND for safety
        return 'AND';
    }

    // Inside ConditionGroup
    private function needsGroupParentheses(): bool
    {
        if ($this->isExplicit) {
            return true;
        }

        foreach ($this->children->all() as $child) {
            $linkName = ($child instanceof ConditionClause)
                ? $child->getLogicalLink()
                : $child->getGroupLink();

            $op = SqlOperator::tryFrom($linkName);

            if ($op && $op->getPrecedence() < SqlOperator::AND->getPrecedence()) {
                return true;
            }
        }

        return false;
    }
}