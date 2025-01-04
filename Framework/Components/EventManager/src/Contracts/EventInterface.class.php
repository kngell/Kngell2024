<?php

declare(strict_types=1);

interface EventInterface
{
    public function getName() : string;

    public function setName(string $name): self;

    public function getObject(): ?object;

    public function setObject(?object $object): self;

    public function setResults(?object $results): self;

    public function getResults(): ?object;

    public function getParams(): mixed;
}
