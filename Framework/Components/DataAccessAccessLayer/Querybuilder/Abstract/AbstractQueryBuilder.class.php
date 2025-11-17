<?php

declare(strict_types=1);
abstract class AbstractQueryBuilder
{
    protected SqlQueryComponent $queryComponent;

    public function getParameters(): array
    {
        return $this->queryComponent->getParameters();
    }

    public function getTableAlias(): array
    {
        return $this->queryComponent->getTableAlias();
    }

    public function getBindArray(): array
    {
        return $this->queryComponent->getBindArr();
    }

    public function getAliasCheck(): array
    {
        return $this->queryComponent->getAliasCheck();
    }

    public function reset(): void
    {
        $this->queryComponent->resetState();
    }

    public function getTables(): array
    {
        return $this->queryComponent->getTables();
    }
    // REMOVE: parameterManager() method entirely

    /**
     * Get all data needed for entity hydration in one call.
     */
    public function getHydrationData(): array
    {
        return [
            'parameters' => $this->getParameters(),
            'tableAlias' => $this->getTableAlias(),
            'bindArray' => $this->getBindArray(),
            'aliasCheck' => $this->getAliasCheck(),
            'logicalToPhysicalMap' => $this->queryComponent->getLogicalToPhysicalMap(),
            'tables' => $this->queryComponent->getTables(),
        ];
    }

    public function getQuery(): string
    {
        return $this->queryComponent->getQuery();
    }
}