<?php

declare(strict_types=1);

interface ClauseBuilderFactoryInterface
{
    public function supports(SqlStatement $statement): bool;

    public function create(): ?ClauseBuilderInterface;
}
