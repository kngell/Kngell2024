<?php

declare(strict_types=1);

final class SqlNullType implements SqlTypeHandlerInterface
{
    public function toSqlLiteral(mixed $normalizedValue, EntityManagerInterface $em): string
    {
        return 'NULL';
    }

    public function fromSqlLiteral(string $sqlLiteral, EntityManagerInterface $em): mixed
    {
        return null;
    }

    public function supports(mixed $normalizedValue): bool
    {
        return $normalizedValue === null;
    }

    public function getSqlDataType(): string
    {
        return 'NULL';
    }
}
