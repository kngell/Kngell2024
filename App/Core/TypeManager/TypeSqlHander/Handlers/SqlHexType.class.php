<?php

declare(strict_types=1);

final class SqlHexType implements SqlTypeHandlerInterface
{
    public function toSqlLiteral(mixed $normalizedValue, EntityManagerInterface $em): string
    {
        if ($normalizedValue === null) {
            return 'NULL';
        }

        if (!is_string($normalizedValue) || !$this->isValidHex($normalizedValue)) {
            throw new InvalidArgumentException('Invalid hex value for SQL');
        }

        return $normalizedValue;
    }

    public function fromSqlLiteral(string $sqlLiteral, EntityManagerInterface $em): mixed
    {
        if ($sqlLiteral === 'NULL' || $sqlLiteral === 'null') {
            return null;
        }

        if (str_starts_with($sqlLiteral, '0x') || str_starts_with($sqlLiteral, '0X')) {
            $hex = substr($sqlLiteral, 2);
            if (ctype_xdigit($hex)) {
                return '0x' . strtolower($hex);
            }
        } elseif (ctype_xdigit($sqlLiteral)) {
            return '0x' . strtolower($sqlLiteral);
        }

        throw new InvalidArgumentException('Invalid hex literal from SQL: ' . $sqlLiteral);
    }

    public function supports(mixed $normalizedValue): bool
    {
        return ($normalizedValue === null) ||
               (is_string($normalizedValue) && $this->isValidHex($normalizedValue));
    }

    public function getSqlDataType(): string
    {
        return 'VARBINARY(255)';
    }

    private function isValidHex(mixed $value): bool
    {
        return is_string($value) &&
               strlen($value) >= 3 &&
               str_starts_with($value, '0x') &&
               ctype_xdigit(substr($value, 2));
    }
}
