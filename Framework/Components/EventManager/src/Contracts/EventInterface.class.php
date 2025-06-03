<?php

declare(strict_types=1);

interface EventInterface
{
    public function getName() : string;

    public function setName(string $name): self;

    public function getObject(): ?object;

    public function setObject(?object $object): self;

    public function setResults(mixed $results): self;

    public function getResults(): mixed;

    public function getParams(): mixed;
}