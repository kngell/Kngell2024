<?php

declare(strict_types=1);

interface RestoreResultInterface
{
    /** Stable lowercase identifier: 'product', 'hero', 'category'. */
    public function entityType(): string;

    public function entityId(): int|string;

    /** True if any row was actually un-archived. */
    public function changed(): bool;

    /** Total rows affected across the cascade. */
    public function affectedRows(): int;
}