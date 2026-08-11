<?php

declare(strict_types=1);

interface RuleFactoryInterface
{
    public function supports(SqlStatement $statement): bool;

    public function create(string $method, mixed $data, ?string $customAlias): QueryRulesInterface;
}