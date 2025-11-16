<?php

declare(strict_types=1);
class GenericQueryFlowValidator implements FlowValidatorInterface
{
    public function __construct(private SqlQueryComponent $query)
    {
    }

    public function validate(array $queryFlow, array $genericMap, array $conditions = []): void
    {
    }

    public function validateInsertFlow($queryFlow, array $insertMap): void
    {
    }
}