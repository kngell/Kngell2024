<?php

declare(strict_types=1);

interface FlowValidatorFactoryInterface
{
    public function supports(SqlStatementType $statement): bool;

    public function create(): FlowValidatorInterface;
}