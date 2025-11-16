<?php

declare(strict_types=1);
class QueryFlowValidatorForUpdate implements FlowValidatorInterface
{
    public function __construct(private SqlQueryComponent $query)
    {
    }

    public function validate(array $queryFlow, array $updateMap, array $conditions = []): void
    {
    }

    public function validateInsertFlow($queryFlow, array $insertMap): void
    {
    }
}