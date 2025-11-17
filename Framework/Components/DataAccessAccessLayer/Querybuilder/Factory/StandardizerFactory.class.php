<?php

declare(strict_types=1);

class StandardizerFactory
{
    public function __construct(private SqlQueryComponent $query)
    {
    }

    public function supports(SqlStatementType $statement): bool
    {
        return $statement === SqlStatementType::INSERT;
    }

    public function create(SqlStatementType $statement): DataStandardizerInterface
    {
        return new InsertDataStandardizer();
    }
}