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
        if ($this->query->hasSet() && !$this->query->hasUpdate()) {
            $this->query->assumeUpdateCurrentTable();
        }
    }

    protected function validateClauseOrder(): void
    {
    }

    protected function shouldBuildClause(string $clause): bool
    {
        return false;
    }

    protected function buildClause(string $clause): void
    {
    }

    protected function buildStatement(?SqlStatement $type = null): void
    {
        $updateMap = $this->query->getUpdateMap();

        if ($this->isBulkUpdate()) {
            $statement = new BulkUpdateStatement(
                $updateMap,
                $this->query->getJoinMap(),
                $this->query->getOnMap(),
                $this->query->getQueryFlow(),
                $this->query->getEntityManager(),
                $this->query->getBulkType(),
            );
        } else {
            $statement = new UpdateStatement(
                $updateMap,
                $this->query->getQueryFlow(),
                $this->query->getEntityManager(),
            );
        }

        $this->query->add($statement);
        $this->query->mergeChildState($statement);
    }

    private function isBulkUpdate(): bool
    {
        $userFlow = array_keys($this->query->getQueryFlow());
        return in_array('bulkUpdate', $userFlow);
    }
}
