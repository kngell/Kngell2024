<?php

declare(strict_types=1);

class DataManipulationClauseBuilderFactory extends AbstractSqlFactory
{
    private const SUPPORTED_TYPES = [
        SqlStatement::INSERT,
        SqlStatement::UPDATE,
        SqlStatement::DELETE,
        SqlStatement::MERGE,
    ];

    public function supports(SqlStatement $statement): bool
    {
        return in_array($statement, self::SUPPORTED_TYPES);
    }

    public function create(): ?ClauseBuilderInterface
    {
        return match ($this->getStatement()) {
            SqlStatement::INSERT => new InsertClauseBuilder($this->component),
            SqlStatement::UPDATE => new UpdateClauseBuilder($this->component),
            SqlStatement::DELETE => new DeleteClauseBuilder($this->component),
            SqlStatement::MERGE => new MergeClauseBuilder($this->component),
            default => null
        };
    }
}
