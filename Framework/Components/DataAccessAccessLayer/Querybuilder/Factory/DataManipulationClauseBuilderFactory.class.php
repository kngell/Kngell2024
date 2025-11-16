<?php

declare(strict_types=1);
class DataManipulationClauseBuilderFactory implements ClauseBuilderFactoryInterface
{
    public function __construct(private SqlQueryComponent $component)
    {
    }

    public function supports(SqlStatementType $statement): bool
    {
        return in_array($statement, [SqlStatementType::INSERT, SqlStatementType::UPDATE, SqlStatementType::DELETE, SqlStatementType::MERGE]);
    }

    public function create(): ?ClauseBuilderInterface
    {
        $statementType = $this->component->getSqlClause();

        return match (true) {
            $statementType === SqlStatementType::INSERT => new InsertClauseBuilder($this->component) ,
            default => null,
        };
        // return new SelectClauseBuilder($this->component);
    }
}