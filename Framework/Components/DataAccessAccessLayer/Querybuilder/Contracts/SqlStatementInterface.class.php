<?php

declare(strict_types=1);

interface SqlStatementInterface
{
    public function getStatement(): ?SqlStatement;
}
