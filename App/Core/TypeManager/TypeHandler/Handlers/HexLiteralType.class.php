<?php

declare(strict_types=1);

final class HexLiteralType implements TypeHandlerInterface
{
    private const HEX_PATTERN = '/^0x[a-fA-F0-9]+$/';
    private const ERROR_INVALID_FORMAT = 'Invalid hex literal format. Expected format: 0x[0-9a-fA-F]+';
    private const ERROR_EMPTY_HEX = 'Hex literal cannot be empty after 0x prefix';

    /**
     * Determine if the value is a valid hex literal.
     */
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        if (!is_string($value)) {
            return false;
        }

        // Fast length check (must have at least 3 chars: "0x" + at least 1 hex digit)
        if (strlen($value) < 3) {
            return false;
        }

        // Check prefix first (faster than full regex)
        if (str_starts_with($value, '0x')) {
            // Validate remaining characters are hex digits
            $hexPart = substr($value, 2);
            return $hexPart !== '' && ctype_xdigit($hexPart);
        }

        return false;
    }

    /**
     * Normalize hex literal for database storage.
     */
    public function normalizeForDatabase(mixed $entityValue, ?ReflectionProperty $property = null): mixed
    {
        if ($entityValue === null) {
            return null;
        }

        // Ensure it's valid before storing
        if (!$this->supports($entityValue)) {
            throw new InvalidArgumentException(self::ERROR_INVALID_FORMAT);
        }

        // Optionally: convert to lowercase for consistency
        return strtolower($entityValue);
    }

    /**
     * Normalize raw database value for entity hydration.
     */
    public function normalizeForEntity(mixed $rawValue, ReflectionProperty $property, object $entityInstance): mixed
    {
        if ($rawValue === null) {
            return null;
        }

        // Validate the raw value from database
        if (is_string($rawValue) && $this->supports($rawValue)) {
            // Optionally: ensure consistent casing when hydrating
            return strtolower($rawValue);
        }

        // Handle case where database contains hex without 0x prefix
        if (is_string($rawValue) && ctype_xdigit($rawValue)) {
            return '0x' . strtolower($rawValue);
        }

        // If we can't normalize, throw exception
        throw new InvalidArgumentException(self::ERROR_INVALID_FORMAT);
    }

    /**
     * Get the supported PHP type.
     */
    public function getSupportedType(): ?string
    {
        return 'string';
    }

    /**
     * Additional helper: Convert hex to decimal.
     */
    public function toDecimal(string $hex): int
    {
        if (!$this->supports($hex)) {
            throw new InvalidArgumentException(self::ERROR_INVALID_FORMAT);
        }

        return hexdec(substr($hex, 2));
    }

    /**
     * Additional helper: Create hex from decimal.
     */
    public function fromDecimal(int $decimal): string
    {
        if ($decimal < 0) {
            throw new InvalidArgumentException('Decimal value cannot be negative for hex conversion');
        }

        return '0x' . dechex($decimal);
    }

    /**
     * Additional helper: Validate hex string (throw exception on failure).
     */
    public function validate(string $hex): void
    {
        if (!$this->supports($hex)) {
            throw new InvalidArgumentException(self::ERROR_INVALID_FORMAT);
        }
    }

    /**
     * Additional helper: Check if hex string represents an even byte boundary.
     */
    public function isCompleteByte(string $hex): bool
    {
        if (!$this->supports($hex)) {
            return false;
        }

        $hexDigits = strlen($hex) - 2; // Remove "0x" prefix
        return $hexDigits % 2 === 0;
    }

    /**
     * Additional helper: Get length in bytes.
     */
    public function getByteLength(string $hex): int
    {
        if (!$this->supports($hex)) {
            throw new InvalidArgumentException(self::ERROR_INVALID_FORMAT);
        }

        return (int) ceil((strlen($hex) - 2) / 2);
    }
}
