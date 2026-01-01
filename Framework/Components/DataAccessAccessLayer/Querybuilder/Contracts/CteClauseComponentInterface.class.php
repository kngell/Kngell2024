<?php

declare(strict_types=1);

interface CteClauseComponentInterface extends ClauseComponentInterface
{
    public function isRecursive(): bool;
}