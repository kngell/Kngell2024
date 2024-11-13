<?php

declare(strict_types=1);
interface QueryParamsInterface
{
    public function params(?string $repositoryMethod = null) : array;

    public function table(?string $tbl = null, mixed $columns = null) : self;

    public function join(?string $tbl = null, mixed $columns = null, string $joinType = 'INNER JOIN') : self;

    public function where(array $conditions, ?string $op = null) : self;

    public function build() : array;

    public function reset() : self;
}
