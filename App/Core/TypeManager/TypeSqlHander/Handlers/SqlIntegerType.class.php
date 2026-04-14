<?php

declare(strict_types=1);

final class SqlIntegerType implements SqlTypeHandlerInterface
{
    public function toSqlLiteral(mixed $normalizedValue, EntityManagerInterface $em): string
    {
        if ($normalizedValue === null) {
            return 'NULL';
        }

        return (string) (int) $normalizedValue;
    }

    public function fromSqlLiteral(string $sqlLiteral, EntityManagerInterface $em): mixed
    {
        if ($sqlLiteral === 'NULL' || $sqlLiteral === 'null') {
            return null;
        }

        if (!is_numeric($sqlLiteral)) {
            throw new InvalidArgumentException('Invalid integer from SQL: ' . $sqlLiteral);
        }

        return (int) $sqlLiteral;
    }

    public function supports(mixed $normalizedValue): bool
    {
        return $normalizedValue === null || is_int($normalizedValue);
    }

    public function getSqlDataType(): string
    {
        return 'INT';
    }
}
