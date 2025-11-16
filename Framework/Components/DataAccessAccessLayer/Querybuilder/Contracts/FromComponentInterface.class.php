<?php

declare(strict_types=1);

/**
 * Common interface for FROM components.
 */
interface FromComponentInterface extends ClauseComponentInterface
{
    public function getTable(): string;

    public function getAlias(): string;

    public function isJoin(): bool;
}
