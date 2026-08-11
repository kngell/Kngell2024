<?php

declare(strict_types=1);
/**
 * DELETE QUERY BUILDER - Data deletion operations.
 */
interface SqlDeleteQueryBuilderInterface extends SqlQueryBuilderInterface, SqlCommonClauseInterface, SqlCommonConditionClauseInterface
{
    public function delete(null|string|Closure $table = null, null|string $alias = null): self;

    public function deleteFrom(string $table): self;
}