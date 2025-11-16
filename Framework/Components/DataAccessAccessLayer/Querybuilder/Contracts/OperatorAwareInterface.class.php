<?php

declare(strict_types=1);
interface OperatorAwareInterface
{
    public function getOperator(): ?SqlOperator;

    public function getLogicalLink(): string;
}