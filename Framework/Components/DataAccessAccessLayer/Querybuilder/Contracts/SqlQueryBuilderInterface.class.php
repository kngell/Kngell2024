<?php

declare(strict_types=1);
/**
 * BASE INTERFACE - Common methods for all query types.
 */
interface SqlQueryBuilderInterface
{
    public function build(): string;

    public function execute(): array;

    public function getStatementType(): SqlStatementType;
}