<?php

declare(strict_types=1);

interface SoftDeletableInterface
{
    public function setDeletedAt(?DateTimeImmutable $deletedAt): SoftDeletableInterface;

    public function getDeletedAt(): ?DateTimeImmutable;

    public function isDeleted(): bool;

    public function softDelete(?DateTimeImmutable $at = null): self;

    public function restore(): self;

    public function touchDeleted(?DateTimeImmutable $at = null): void;

    public function getDateFormat(): string;
}