<?php

declare(strict_types=1);

interface SqlCommonConditionClauseInterface
{
    // ===========================================
    // WHERE CONDITIONS (SELECT-specific)
    // ===========================================
    public function where(mixed ...$conditions): static;

    public function whereEqualTo(string $col, mixed $val): static;

    public function whereNotEqualTo(string $col, mixed $val): static;

    public function whereLessThan(string $col, mixed $val): static;

    public function whereGreaterThan(string $col, mixed $val): static;

    public function whereLike(string $col, string $pattern): static;

    public function whereNotLike(string $col, string $pattern): static;

    public function whereIn(mixed ...$values): static;

    public function whereNotIn(mixed ...$values): static;

    public function whereNull(string $col): static;

    public function whereNotNull(string $col): static;

    public function whereBetween(string $col, mixed $min, mixed $max): static;

    // ===========================================
    // LOGICAL COMBINATION
    // ===========================================
    public function andWhere(mixed ...$conditions): static;

    public function orWhere(mixed ...$conditions): static;

    public function and(mixed ...$conditions): static;   // alias

    public function or(mixed ...$conditions): static;   // alias
}