<?php

declare(strict_types=1);

final class SqlTypeHandlerFactory
{
    private array $handlers = [];
    private array $handlerCache = [];

    public function __construct()
    {
        $this->registerDefaultHandlers();
    }

    public function register(string $type, SqlTypeHandlerInterface $handler): void
    {
        $this->handlers[$type] = $handler;
        $this->handlerCache = []; // Clear cache
    }

    public function getForValue(mixed $normalizedValue): SqlTypeHandlerInterface
    {
        $type = $this->determineType($normalizedValue);

        if (!isset($this->handlerCache[$type])) {
            $this->handlerCache[$type] = $this->handlers[$type] ??
                throw new RuntimeException("No SQL type handler for type: $type");
        }

        return $this->handlerCache[$type];
    }

    private function determineType(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        // Hex/binary values (from BinaryType/HexLiteralType)
        if (is_string($value) && $this->isHexLiteral($value)) {
            return 'hex';
        }

        // Standard PHP types
        if (is_int($value)) {
            return 'integer';
        }

        if (is_float($value)) {
            return 'float';
        }

        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_string($value)) {
            return 'string';
        }

        // Arrays could be JSON
        if (is_array($value)) {
            return 'json';
        }

        return 'string'; // Default fallback
    }

    private function isHexLiteral(mixed $value): bool
    {
        return is_string($value) &&
               strlen($value) >= 3 &&
               str_starts_with($value, '0x') &&
               ctype_xdigit(substr($value, 2));
    }

    private function registerDefaultHandlers(): void
    {
        $this->register('null', new SqlNullType());
        $this->register('string', new SqlStringType());
        $this->register('integer', new SqlIntegerType());
        $this->register('float', new SqlFloatType());
        $this->register('boolean', new SqlBooleanType());
        $this->register('hex', new SqlHexType());
        $this->register('json', new SqlJsonType());
    }
}
