<?php

declare(strict_types=1);

interface ResultFetcherInterface
{
    public function fetchAll(): array;

    public function fetchFirst(): mixed;

    public function fetchColumn(int $columnIndex = 0): array;

    public function fetchKeyPairs(): array;

    public function count(): int;
}