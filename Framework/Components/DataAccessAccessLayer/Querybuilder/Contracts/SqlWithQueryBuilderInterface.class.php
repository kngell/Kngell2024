<?php

declare(strict_types=1);
interface SqlWithQueryBuilderInterface extends SqlSelectQueryBuilderInterface
{
    public function with(string $cteName, Closure $cteBodyCallback): SqlWithQueryBuilderInterface;

    public function withRecursive(string $cteName, Closure $cteBodyCallback): SqlWithQueryBuilderInterface;

    public function select(string|array|Closure ...$columns): SqlSelectQueryBuilderInterface;
}