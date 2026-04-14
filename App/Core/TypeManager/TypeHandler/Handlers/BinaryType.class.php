<?php

declare(strict_types=1);

final class BinaryType implements TypeHandlerInterface
{
    private const HEX_PREFIX = '0x';
    private const ERROR_INVALID_BINARY = 'Invalid binary data';
    private const ERROR_EMPTY_BINARY = 'Binary data cannot be empty';

    /**
     * Determine if the value is binary data
     * Supports: raw binary string, hex string (with or without 0x prefix), resource streams.
     */
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        if ($value === null) {
            return false;
        }

        // Handle raw binary strings
        if (is_string($value)) {
            // Check if string contains non-printable characters
            return !$this->isPrintableString($value) && $value !== '';
        }

        // Handle resources (file pointers, streams)
        if (is_resource($value)) {
            return get_resource_type($value) === 'stream';
        }

        // Handle hex strings that represent binary
        if (is_string($value) && $this->isHexString($value)) {
            return true;
        }

        return false;
    }

    /**
     * Normalize binary data for database storage
     * Converts various formats to hex string for database.
     */
    public function normalizeForDatabase(mixed $entityValue, ?ReflectionProperty $property = null): mixed
    {
        if ($entityValue === null) {
            return null;
        }

        try {
            // Convert to raw binary first
            $binary = $this->toBinaryString($entityValue);

            // Validate binary is not empty (if that's a requirement)
            if ($binary === '') {
                throw new InvalidArgumentException(self::ERROR_EMPTY_BINARY);
            }

            // Convert to hex for database (with 0x prefix for MySQL/MariaDB)
            return self::HEX_PREFIX . bin2hex($binary);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                sprintf('Failed to normalize binary data: %s', $e->getMessage()),
                0,
                $e,
            );
        }
    }

    /**
     * Normalize raw database value for entity hydration.
     */
    public function normalizeForEntity(mixed $rawValue, ReflectionProperty $property, object $entityInstance): mixed
    {
        if ($rawValue === null) {
            return null;
        }

        // Database returns hex string (with 0x prefix) or raw binary depending on driver
        if (is_string($rawValue)) {
            // If it's a hex string with prefix, convert to binary
            if (str_starts_with($rawValue, self::HEX_PREFIX)) {
                $hex = substr($rawValue, 2);
                if ($hex === '' || !ctype_xdigit($hex)) {
                    throw new InvalidArgumentException(self::ERROR_INVALID_BINARY);
                }
                return hex2bin($hex) ?: throw new InvalidArgumentException(self::ERROR_INVALID_BINARY);
            }

            // If it's already raw binary, return as-is
            if (!$this->isPrintableString($rawValue)) {
                return $rawValue;
            }

            // If it's a hex string without prefix
            if (ctype_xdigit($rawValue)) {
                return hex2bin($rawValue) ?: throw new InvalidArgumentException(self::ERROR_INVALID_BINARY);
            }
        }

        // If we can't handle it, throw exception
        throw new InvalidArgumentException(sprintf(
            'Unsupported binary format for entity hydration: %s',
            gettype($rawValue),
        ));
    }

    /**
     * Get the supported PHP type.
     */
    public function getSupportedType(): ?string
    {
        return 'binary';
    }

    /**
     * Helper: Get binary length in bytes.
     */
    public function getLength(mixed $value): int
    {
        try {
            $binary = $this->toBinaryString($value);
            return strlen($binary);
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Helper: Compare two binary values.
     */
    public function equals(mixed $value1, mixed $value2): bool
    {
        try {
            $binary1 = $this->toBinaryString($value1);
            $binary2 = $this->toBinaryString($value2);
            return hash_equals($binary1, $binary2);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Helper: Convert to base64 for safe transmission/storage.
     */
    public function toBase64(mixed $value): string
    {
        $binary = $this->toBinaryString($value);
        return base64_encode($binary);
    }

    /**
     * Helper: Create from base64 string.
     */
    public function fromBase64(string $base64): string
    {
        $binary = base64_decode($base64, true);
        if ($binary === false) {
            throw new InvalidArgumentException('Invalid base64 string');
        }
        return $binary;
    }

    /**
     * Helper: Convert various input types to raw binary string.
     */
    private function toBinaryString(mixed $value): string
    {
        if (is_string($value)) {
            // If it's already raw binary, return as-is
            if (!$this->isPrintableString($value)) {
                return $value;
            }

            // If it's a hex string, convert to binary
            if ($this->isHexString($value)) {
                $hex = str_starts_with($value, self::HEX_PREFIX)
                    ? substr($value, 2)
                    : $value;

                $binary = hex2bin($hex);
                if ($binary === false) {
                    throw new InvalidArgumentException('Invalid hex string for binary conversion');
                }
                return $binary;
            }

            // If it's a regular string, treat as UTF-8
            return $value;
        }

        if (is_resource($value) && get_resource_type($value) === 'stream') {
            return stream_get_contents($value);
        }

        throw new InvalidArgumentException(sprintf(
            'Cannot convert type "%s" to binary string',
            gettype($value),
        ));
    }

    /**
     * Helper: Check if string is a hex representation.
     */
    private function isHexString(string $value): bool
    {
        // Hex with prefix
        if (str_starts_with($value, self::HEX_PREFIX)) {
            $hexPart = substr($value, 2);
            return $hexPart !== '' && ctype_xdigit($hexPart);
        }

        // Hex without prefix
        return ctype_xdigit($value);
    }

    /**
     * Helper: Check if string is printable (non-binary)
     * More accurate than ctype_print() which fails on extended ASCII.
     */
    private function isPrintableString(string $value): bool
    {
        if ($value === '') {
            return true;
        }

        // Check for null bytes (definitely binary)
        if (str_contains($value, "\0")) {
            return false;
        }

        // Check if string is valid UTF-8
        if (mb_check_encoding($value, 'UTF-8')) {
            // For UTF-8, check if all characters are printable
            return preg_match('/^[\p{L}\p{N}\p{P}\p{Z}\p{S}]+$/u', $value) === 1;
        }

        // For non-UTF-8, use more lenient check
        return preg_match('/^[\x20-\x7E\xA0-\xFF]*$/', $value) === 1;
    }
}
