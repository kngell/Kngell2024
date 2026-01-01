<?php

declare(strict_types=1);

class UpdateClauseBuilder extends AbstractClauseBuilder implements ClauseBuilderInterface
{
    public function __construct(
        private SqlUpdateQuery $query,
    ) {
    }

    protected function ensureMinimalFlow(): void
    {
        if ($this->query->hasUpdate() && !$this->query->hasSet()) {
            throw new QueryFlowException('You need to specify what you want to update using the set() method.');
        }

        if ($this->query->hasSet() && !$this->query->hasUpdate()) {
            $this->query->assumeUpdateCurrentTable();
        }

        // Validate we have at least the minimal required
        if (!$this->query->isClosure() && (!$this->query->hasUpdate() || !$this->query->hasSet())) {
            throw new QueryFlowException(
                'Query must have at least UPDATE and SET clauses. ' .
                'Called update(): ' . ($this->query->hasUpdate() ? 'yes' : 'no') . ', ' .
                'Called set(): ' . ($this->query->hasSet() ? 'yes' : 'no'),
            );
        }
    }

    protected function validateClauseOrder(): void
    {
        $userFlow = array_keys($this->query->getQueryFlow());
        $statementType = $this->query->getSqlStatementType();
        $categoryOrder = $statementType->getCategoryBuildOrder();

        $this->validateAllowedMethods($userFlow, $statementType);
        $this->validateCategoryOrder($userFlow, $categoryOrder);
    }

    protected function shouldBuildClause(string $clause): bool
    {
        $userFlow = array_keys($this->query->getQueryFlow());
        return in_array($clause, $userFlow);
    }

    protected function buildClause(string $clause): void
    {
    }

    protected function buildStatement(?SqlStatementType $type = null): void
    {
        $statement = new UpdateStatement(
            $this->query->getUpdateMap(),
            $this->query->getQueryFlow(),
            $this->query->getEntityManager(),
        );
        $statement->build();
    }
}