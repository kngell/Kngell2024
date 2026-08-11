<?php

declare(strict_types=1);

interface SqlCaseblockBuilderInterface extends SqlQueryBuilderInterface, SqlCommonConditionClauseInterface
{
    public function case(mixed $expression): static;

    public function when(mixed ...$conditions): static;

    public function then(mixed ...$result): static;

    public function else(mixed ...$sqlExpression): static;

    public function end(?string $as = null): static;
}