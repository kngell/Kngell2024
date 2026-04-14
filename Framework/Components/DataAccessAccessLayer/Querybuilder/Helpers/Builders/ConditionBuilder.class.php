<?php

declare(strict_types=1);

class ConditionBuilder
{
    private array $groups = [];
    private ?ConditionGroup $currentGroup = null;

    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function addCondition(string $method, array $conditions): void
    {
        $clauseType = $this->determineClauseType($method);
        $isOrCondition = $this->isOrCondition($method);

        // If we have no current group, or the clause type changed, start a new group
        if ($this->currentGroup === null || $this->currentGroup->getSqlClause() !== $clauseType) {
            $this->startNewGroup($clauseType, $method, $conditions);
            return;
        }

        // If this is an OR condition and we already have conditions in the group,
        // we should start a new group to ensure proper OR semantics
        if ($isOrCondition && !$this->currentGroup->isEmpty()) {
            $this->startNewGroup($clauseType, $method, $conditions);
            return;
        }

        // Otherwise, add to current group
        $this->addToCurrentGroup($method, $conditions);
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    private function startNewGroup(SqlClause $clauseType, string $method, array $conditions): void
    {
        $this->currentGroup = new ConditionGroup($clauseType);
        $condition = new ConditionClause($conditions, $method, $this->em);
        $this->currentGroup->add($condition);
        $this->groups[] = $this->currentGroup;
    }

    private function addToCurrentGroup(string $method, array $conditions): void
    {
        $condition = new ConditionClause($conditions, $method, $this->em);
        $this->currentGroup->add($condition);
    }

    private function determineClauseType(string $method): SqlClause
    {
        return match(true) {
            str_starts_with($method, 'where') => SqlClause::WHERE,
            str_starts_with($method, 'having') => SqlClause::HAVING,
            default => SqlClause::WHERE
        };
    }

    private function isOrCondition(string $method): bool
    {
        return str_starts_with($method, 'or');
    }
}