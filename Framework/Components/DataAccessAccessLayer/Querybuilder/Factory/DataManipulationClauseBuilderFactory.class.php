<?php

declare(strict_types=1);

class DataManipulationClauseBuilderFactory extends AbstractSqlFactory
{
    private const SUPPORTED_TYPES = [
        SqlStatementType::INSERT,
        SqlStatementType::UPDATE,
        SqlStatementType::DELETE,
        SqlStatementType::MERGE,
    ];

    public function supports(SqlStatementType $statement): bool
    {
        return in_array($statement, self::SUPPORTED_TYPES);
    }

    public function create(): ?ClauseBuilderInterface
    {
        return match ($this->getStatementType()) {
            SqlStatementType::INSERT => new InsertClauseBuilder($this->component),
            SqlStatementType::UPDATE => new UpdateClauseBuilder($this->component),
            SqlStatementType::DELETE => new DeleteClauseBuilder($this->component),
            SqlStatementType::MERGE => new MergeClauseBuilder($this->component),
            default => null
        };
    }
}