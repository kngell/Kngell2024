<?php

declare(strict_types=1);

interface SoftDeletableInterface
{
    public function setDeletedAt(?DateTimeImmutable $deletedAt): self;

    public function getDeletedAt(): ?DateTimeImmutable;

    public function isDeleted(): bool;

    public function softDelete(): void;
}