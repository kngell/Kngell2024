<?php

declare(strict_types=1);

interface TimestampableInterface
{
    public function setCreatedAt(DateTimeImmutable $createdAt): TimestampableInterface;

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): TimestampableInterface;

    public function getCreatedAt(): ?DateTimeImmutable;

    public function getUpdatedAt(): ?DateTimeImmutable;
}