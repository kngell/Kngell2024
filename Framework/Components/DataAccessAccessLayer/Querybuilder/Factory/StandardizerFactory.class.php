<?php

declare(strict_types=1);

class StandardizerFactory
{
    public function __construct()
    {
    }

    public function supports(SqlStatement $statement): bool
    {
        return in_array($statement, [SqlStatement::INSERT, SqlStatement::SELECT, SqlStatement::UPDATE, SqlStatement::DELETE]);
    }

    public function create(SqlStatement $statement): DataStandardizerInterface
    {
        return match (true) {
            $statement === SqlStatement::INSERT => new InsertDataStandardizer(),
            $statement === SqlStatement::SELECT => new SelectDataStandardizer(),
            $statement === SqlStatement::UPDATE => new UpdateDataStandardizer(),
            $statement === SqlStatement::DELETE => new DeleteDataStandardizer(),
            default => new NullObject($this)
        };
    }
}
