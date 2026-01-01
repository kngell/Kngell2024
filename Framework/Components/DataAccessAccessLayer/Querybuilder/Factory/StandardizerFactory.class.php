<?php

declare(strict_types=1);

class StandardizerFactory
{
    public function __construct()
    {
    }

    public function supports(SqlStatementType $statement): bool
    {
        return in_array($statement, [SqlStatementType::INSERT, SqlStatementType::SELECT, SqlStatementType::UPDATE]);
    }

    public function create(SqlStatementType $statement): DataStandardizerInterface
    {
        return match (true) {
            $statement === SqlStatementType::INSERT => new InsertDataStandardizer(),
            $statement === SqlStatementType::SELECT => new SelectDataStandardizer(),
            $statement === SqlStatementType::UPDATE => new UpdateDataStandardizer(),
            default => new NullObject($this)
        };
    }
}