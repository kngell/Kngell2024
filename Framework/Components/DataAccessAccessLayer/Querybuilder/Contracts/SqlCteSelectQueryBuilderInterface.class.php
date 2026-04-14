<?php

declare(strict_types=1);

interface SqlCteSelectQueryBuilderInterface extends SqlQueryBuilderInterface
{
    public function with(string $cteTableName): self;

    public function withRecursive(string $cteTableName): self;

    public function body(SqlSelectQueryBuilderInterface|Closure $cteBody): self;

    public function mainQuery(SqlSelectQueryBuilderInterface|Closure $mainQuery): self;

    public function cycle(?string $cycleColumn = null): self;
}