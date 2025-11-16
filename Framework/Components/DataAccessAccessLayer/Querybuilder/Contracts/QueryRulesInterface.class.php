<?php

declare(strict_types=1);

interface QueryRulesInterface
{
    public function initialize(QueryState $state): void;

    public function getRule(array $conditions): string;

    // Return the updated state after rule processing
    public function getState(): QueryState;
}