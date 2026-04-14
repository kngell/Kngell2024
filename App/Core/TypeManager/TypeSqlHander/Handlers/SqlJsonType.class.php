<?php

declare(strict_types=1);

final class SqlJsonType implements SqlTypeHandlerInterface
{
    public function toSqlLiteral(mixed $normalizedValue, EntityManagerInterface $em): string
    {
        if ($normalizedValue === null) {
            return 'NULL';
        }

        // Database value should already be JSON string from ArrayType handler
        if (!is_string($normalizedValue) || !$this->isJson($normalizedValue)) {
            // Convert array/object to JSON
            $json = json_encode($normalizedValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) {
                throw new InvalidArgumentException('Invalid JSON data');
            }
            $normalizedValue = $json;
        }

        // JSON needs to be quoted as a string in SQL
        return $em->quote($normalizedValue);
    }

    public function fromSqlLiteral(string $sqlLiteral, EntityManagerInterface $em): mixed
    {
        if ($sqlLiteral === 'NULL' || $sqlLiteral === 'null') {
            return null;
        }

        // Remove SQL quotes
        $value = $sqlLiteral;
        if (strlen($value) >= 2) {
            $first = $value[0];
            $last = $value[strlen($value) - 1];
            if (($first === "'" && $last === "'") || ($first === '"' && $last === '"')) {
                $value = substr($value, 1, -1);
                $value = str_replace($first . $first, $first, $value);
            }
        }

        // Parse JSON
        $decoded = json_decode($value, true);
        if ($decoded === null && $value !== 'null') {
            throw new InvalidArgumentException('Invalid JSON from SQL: ' . $value);
        }

        return $decoded;
    }

    public function supports(mixed $normalizedValue): bool
    {
        if ($normalizedValue === null) {
            return true;
        }

        // Check if it's already a JSON string
        if (is_string($normalizedValue) && $this->isJson($normalizedValue)) {
            return true;
        }

        // Or if it's an array/object that can be JSON encoded
        return is_array($normalizedValue) || is_object($normalizedValue);
    }

    public function getSqlDataType(): string
    {
        return 'JSON';
    }

    private function isJson(string $string): bool
    {
        if (trim($string) === '') {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
