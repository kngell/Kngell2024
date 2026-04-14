<?php

declare(strict_types=1);

final class SqlBooleanType implements SqlTypeHandlerInterface
{
    public function toSqlLiteral(mixed $normalizedValue, EntityManagerInterface $em): string
    {
        if ($normalizedValue === null) {
            return 'NULL';
        }

        // Database value is already 1 or 0 from StandardType
        return $normalizedValue ? '1' : '0';
    }

    public function fromSqlLiteral(string $sqlLiteral, EntityManagerInterface $em): mixed
    {
        if ($sqlLiteral === 'NULL' || $sqlLiteral === 'null') {
            return null;
        }

        // SQL returns booleans as 1/0, true/false, or '1'/'0'
        if ($sqlLiteral === '1' || $sqlLiteral === "'1'" || $sqlLiteral === 'true' || $sqlLiteral === "'true'") {
            return true;
        }

        if ($sqlLiteral === '0' || $sqlLiteral === "'0'" || $sqlLiteral === 'false' || $sqlLiteral === "'false'") {
            return false;
        }

        // Try numeric conversion
        if (is_numeric($sqlLiteral)) {
            return (bool) (int) $sqlLiteral;
        }

        throw new InvalidArgumentException('Invalid boolean from SQL: ' . $sqlLiteral);
    }

    public function supports(mixed $normalizedValue): bool
    {
        return $normalizedValue === null || is_bool($normalizedValue);
    }

    public function getSqlDataType(): string
    {
        return 'TINYINT(1)';
    }
}
