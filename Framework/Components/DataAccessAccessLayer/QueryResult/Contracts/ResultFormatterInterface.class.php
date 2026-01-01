<?php

declare(strict_types=1);
interface ResultFormatterInterface
{
    public function asArray(): array;

    public function asClass(string $className): array;

    public function asObject(): array;

    public function asColumn(int $columnIndex = 0): array;

    public function asKeyPairs(): array;

    public function firstAsArray(): ?array;

    public function firstAsClass(string $className): ?object;
}