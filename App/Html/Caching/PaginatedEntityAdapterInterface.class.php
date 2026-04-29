<?php

declare(strict_types=1);

interface PaginatedEntityAdapterInterface
{
    public function getEntityClass(): string;

    public function getAllKeys(int $page, int $perPage): array;

    public function getEntitiesByIdentifiers(array $identifiers): array;

    public function getTotalCount(): int;

    public function normalizeIdentifier(string $identifier): string;

    public function getIdentifierPrefix(): string;
}