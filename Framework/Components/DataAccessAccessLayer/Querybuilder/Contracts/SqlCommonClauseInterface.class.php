<?php

declare(strict_types=1);
interface SqlCommonClauseInterface
{
    public function from(mixed $table = null, ?string $alias = null): static;

    // ===========================================
    // JOIN CONDITIONS
    // ===========================================
    public function on(mixed ...$onConditions): static;

    public function onEqualTo(string $leftCol, string $rightCol): static;

    public function onNotEqualTo(string $leftCol, string $rightCol): static;

    public function andOn(mixed ...$onConditions): static;

    public function onValue(mixed ...$onConditions): static;

    public function orOnValue(mixed ...$onConditions): static;
}