<?php

declare(strict_types=1);

interface ClauseComponentInterface
{
    public function getSqlClause(): ?SqlClause;

    // public function setLogicalLink(?string $link): void;
}