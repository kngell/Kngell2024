<?php

declare(strict_types=1);

interface TimestampableInterface
{
    public function setCreatedAt(DateTimeImmutable $createdAt): self;

    public function setUpdatedAt(DateTimeImmutable $updatedAt): self;

    public function getCreatedAt(): ?DateTimeImmutable;

    public function getUpdatedAt(): ?DateTimeImmutable;
}