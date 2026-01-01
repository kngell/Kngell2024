<?php

declare(strict_types=1);
class DataQueryClauseBuilderFactory implements ClauseBuilderFactoryInterface
{
    public function __construct(private SqlComponent $component)
    {
    }

    public function supports(SqlStatementType $statement): bool
    {
        return $statement === SqlStatementType::SELECT;
    }

    public function create(): ClauseBuilderInterface
    {
        return new SelectClauseBuilder($this->component);
    }
}
