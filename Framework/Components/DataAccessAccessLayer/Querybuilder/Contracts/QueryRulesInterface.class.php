<?php

declare(strict_types=1);

interface QueryRulesInterface
{
    public function initialize(QueryState $state): void;

    public function getRule(array $conditions): string;

    public function getState(): QueryState;

    public function getMethod(): ?string;
}