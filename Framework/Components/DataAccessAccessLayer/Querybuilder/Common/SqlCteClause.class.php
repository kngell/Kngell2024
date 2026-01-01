<?php

declare(strict_types=1);

enum SqlCteClause: string
{
    public function isRecursive(): bool
    {
        return $this === self::WITH_RECURSIVE;
    }

    public function getKeyword(): string
    {
        return $this->value;
    }
    case WITH = 'WITH';
    case WITH_RECURSIVE = 'WITH RECURSIVE';
}