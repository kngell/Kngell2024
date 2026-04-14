<?php

declare(strict_types=1);

class DeleteClauseBuilder extends AbstractClauseBuilder implements ClauseBuilderInterface
{
    public function __construct(
        private SqlDeleteQuery $query,
    ) {
    }

    protected function ensureMinimalFlow(): void
    {
        if ($this->query->hasDelete() && !$this->query->hasFrom()) {
            throw new QueryFlowException('You need to specify the table from where you want to delete data.');
        }
    }

    protected function validateClauseOrder(): void
    {
        // $userFlow = array_keys($this->query->getQueryFlow());
        // $statementType = $this->query->getStatement();
        // $categoryOrder = $statementType->getCategoryBuildOrder();

        // $this->validateAllowedMethods($userFlow, $statementType);
        // $this->validateCategoryOrder($userFlow, $categoryOrder);
    }

    protected function shouldBuildClause(string $clause): bool
    {
        // $userFlow = array_keys($this->query->getQueryFlow());
        // return in_array($clause, $userFlow);
        return false;
    }

    protected function buildClause(string $clause): void
    {
    }

    protected function buildStatement(?SqlStatement $type = null): void
    {
        $statement = new DeleteStatement(
            $this->query->getDeleteMap(),
            $this->query->getQueryFlow(),
            $this->query->getEntityManager(),
        );
        $this->query->add($statement);
    }
}
