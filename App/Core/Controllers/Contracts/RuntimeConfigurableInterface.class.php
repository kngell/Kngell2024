<?php

declare(strict_types=1);

interface RuntimeConfigurableInterface
{
    /**
     * @param array<string, mixed> $params
     */
    public function configure(array $params): static;
}