<?php

declare(strict_types=1);

interface EventInterface
{
    public function getName(): string;

    public function getObject(): ?object;

    public function setResults(mixed $results): self;

    public function getResults(): array;

    public function getParams(): mixed;

    public function addResult(string $key, mixed $value): self;

    // In EventInterface — add:
    public function isPropagationStopped(): bool;

    public function stopPropagation(): void;

    public function getData(): EventDataDTO;
}