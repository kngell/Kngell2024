<?php

declare(strict_types=1);

interface DeletionResultInterface
{
    /**
     * Stable, lowercase identifier for the aggregate, e.g. 'product', 'hero', 'category'.
     */
    public function entityType(): string;

    /**
     * Primary key of the deleted entity. int|string covers numeric ids and UUIDs.
     */
    public function entityId(): int|string;

    public function changed(): bool;

    public function affectedRows(): int;

    public function deletionMode(): string;
}