<?php

declare(strict_types=1);

final class SqlStringType implements SqlTypeHandlerInterface
{
    public function toSqlLiteral(mixed $normalizedValue, EntityManagerInterface $em): string
    {
        if ($normalizedValue === null) {
            return 'NULL';
        }

        return $em->quote((string) $normalizedValue);
    }

    public function fromSqlLiteral(string $sqlLiteral, EntityManagerInterface $em): mixed
    {
        if ($sqlLiteral === 'NULL' || $sqlLiteral === 'null') {
            return null;
        }
        $value = $sqlLiteral;
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === "'" && $last === "'") || ($first === '"' && $last === '"')) {
                $value = substr($value, 1, -1);
                $value = str_replace($first . $first, $first, $value);
            }
        }

        return $value;
    }

    public function supports(mixed $normalizedValue): bool
    {
        return is_string($normalizedValue) || $normalizedValue === null;
    }

    public function getSqlDataType(): string
    {
        return 'VARCHAR(255)';
    }
}
