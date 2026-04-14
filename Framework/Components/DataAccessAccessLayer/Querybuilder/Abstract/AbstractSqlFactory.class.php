<?php

declare(strict_types=1);

abstract class AbstractSqlFactory implements ClauseBuilderFactoryInterface
{
    public function __construct(protected SqlComponent $component)
    {
    }

    abstract public function supports(SqlStatement $statement): bool;

    protected function getStatement(): ?SqlStatement
    {
        return $this->component->getStatement();
    }
}
