<?php

declare(strict_types=1);

abstract class AbstractSqlFactory implements ClauseBuilderFactoryInterface
{
    public function __construct(protected SqlComponent $component)
    {
    }

    abstract public function supports(SqlStatementType $statement): bool;

    protected function getStatementType(): ?SqlStatementType
    {
        return $this->component->getSqlStatementType();
    }
}