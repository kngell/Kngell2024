<?php

declare(strict_types=1);

trait RestoreResultTrait
{
    public function entityType(): string
    {
        return $this->entityType;
    }

    public function entityId(): int|string
    {
        return $this->entityId;
    }

    public function changed(): bool
    {
        return $this->changed;
    }

    public function affectedRows(): int
    {
        return $this->affectedRows;
    }
}