<?php

declare(strict_types=1);

interface ClauseBuilderFactoryInterface
{
    public function supports(SqlStatementType $statement): bool;

    public function create(): ?ClauseBuilderInterface;
}