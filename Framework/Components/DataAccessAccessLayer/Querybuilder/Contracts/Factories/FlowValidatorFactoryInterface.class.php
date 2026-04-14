<?php

declare(strict_types=1);

interface FlowValidatorFactoryInterface
{
    public function supports(SqlStatement $statement): bool;

    public function create(): FlowValidatorInterface;
}
