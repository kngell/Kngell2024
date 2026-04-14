<?php

declare(strict_types=1);

interface EventInterface
{
    public function getName(): string;

    public function getObject(): ?object;

    public function setResults(mixed $results): self;

    public function getResults(): mixed;

    public function getParams(): mixed;

    public function addResult(string $key, mixed $value): self;
}