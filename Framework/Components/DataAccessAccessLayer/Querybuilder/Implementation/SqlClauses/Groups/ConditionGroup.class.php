<?php

declare(strict_types=1);

class ConditionGroup extends SqlQuery implements ClauseComponentInterface, OperatorAwareInterface
{
    private array $conditions = [];
    private ?string $groupLink = null;

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

        // Apply group-level parentheses only when needed
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
        // ConditionGroups don't have a single operator, return null
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

    /**
     * Apply parentheses to individual conditions based on precedence.
     */
    private function applyConditionParentheses(
        OperatorAwareInterface $condition,
        string $conditionSql,
        ?SqlOperator $previousOperator,
    ): string {
        $currentOperator = $condition->getOperator();
        $logicalLink = $condition->getLogicalLink();

        if (OperatorPrecedence::requiresParentheses($previousOperator, $currentOperator, $logicalLink)) {
            return "({$conditionSql})";
        }

        return $conditionSql;
    }

    /**
     * Apply parentheses to the entire group if needed.
     */
    private function applyGroupParentheses(string $sql): string
    {
        if ($this->needsGroupParentheses()) {
            return "({$sql})";
        }

        return $sql;
    }

    private function needsGroupParentheses(): bool
    {
        $childCount = $this->children->count();

        // Single child rarely needs parentheses
        if ($childCount === 1) {
            $firstChild = $this->children->first();
            return $firstChild instanceof ConditionGroup ||
                   ($firstChild instanceof ConditionClause && $firstChild->getLogicalLink() === 'OR');
        }

        // Multiple children need parentheses if they have OR operators
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

    private function countConditions(): int
    {
        return count($this->conditions);
    }
}