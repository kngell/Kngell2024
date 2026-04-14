<?php

declare(strict_types=1);

interface SqlTypeHandlerInterface
{
    public function toSqlLiteral(mixed $normalizedValue, EntityManagerInterface $em): string;

    public function fromSqlLiteral(string $sqlLiteral, EntityManagerInterface $em): mixed;

    public function supports(mixed $normalizedValue): bool;

    public function getSqlDataType(): string;
}
