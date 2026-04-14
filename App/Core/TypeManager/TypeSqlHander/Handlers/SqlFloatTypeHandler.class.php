<?php

declare(strict_types=1);

final class SqlFloatType implements SqlTypeHandlerInterface
{
    public function toSqlLiteral(mixed $normalizedValue, EntityManagerInterface $em): string
    {
        if ($normalizedValue === null) {
            return 'NULL';
        }

        // Ensure it's a float and format properly
        $floatValue = (float) $normalizedValue;

        // Handle special float values
        if (is_nan($floatValue)) {
            return "'NaN'";
        }

        if (is_infinite($floatValue)) {
            return $floatValue > 0 ? "'Infinity'" : "'-Infinity'";
        }

        return (string) $floatValue;
    }

    public function fromSqlLiteral(string $sqlLiteral, EntityManagerInterface $em): mixed
    {
        if ($sqlLiteral === 'NULL' || $sqlLiteral === 'null') {
            return null;
        }

        // Handle special float values
        if ($sqlLiteral === "'NaN'" || $sqlLiteral === 'NaN') {
            return NAN;
        }

        if ($sqlLiteral === "'Infinity'" || $sqlLiteral === 'Infinity') {
            return INF;
        }

        if ($sqlLiteral === "'-Infinity'" || $sqlLiteral === '-Infinity') {
            return -INF;
        }

        if (!is_numeric($sqlLiteral)) {
            throw new InvalidArgumentException('Invalid float from SQL: ' . $sqlLiteral);
        }

        return (float) $sqlLiteral;
    }

    public function supports(mixed $normalizedValue): bool
    {
        return $normalizedValue === null || is_float($normalizedValue);
    }

    public function getSqlDataType(): string
    {
        return 'DOUBLE';
    }
}
