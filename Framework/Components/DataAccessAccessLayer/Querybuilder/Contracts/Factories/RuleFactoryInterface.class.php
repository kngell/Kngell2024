<?php

declare(strict_types=1);

interface RuleFactoryInterface
{
    public function supports(SqlStatementType $statement): bool;

    public function create(string $method, mixed $data): QueryRulesInterface;
}