<?php

declare(strict_types=1);

class ArrayType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        if (is_array($value)) {
            return true;
        }

        if (is_string($value)) {
            return $this->isJsonArrayString($value);
        }

        return false;
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): mixed
    {
        if ($value === null || $value === [] || $value === '') {
            return null;
        }

        if (!is_array($value)) {
            // Check if it's already a JSON array string
            if (is_string($value) && $this->isJsonArrayString($value)) {
                return $value; // Already JSON, return as-is
            }

            throw new InvalidArgumentException(sprintf(
                'Value must be an array for database normalization. Got: %s',
                gettype($value),
            ));
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, object $contextEntity): mixed
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            // Only return if it's actually an array after decoding
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            // If it's a non-array JSON value (like "16"), return empty array
            return [];
        }

        // For any other type (int, bool, object, etc.), return empty array
        return [];
    }

    private function isJsonArrayString(string $value): bool
    {
        $trimmed = trim($value);

        // Quick checks for obviously invalid values
        if ($trimmed === '' || $trimmed === 'null') {
            return false;
        }

        // Must start with [ and end with ] to be a JSON array
        if (!str_starts_with($trimmed, '[') || !str_ends_with($trimmed, ']')) {
            return false;
        }

        // Actually decode to verify
        $decoded = json_decode($trimmed, true);

        return json_last_error() === JSON_ERROR_NONE && is_array($decoded);
    }

    /**
     * Alternative: More strict validation for specific use cases.
     */
    private function isJsonArrayStringStrict(string $value): bool
    {
        $trimmed = trim($value);

        if ($trimmed === '' || $trimmed === 'null' || $trimmed === '[]') {
            return false; // Or true, depending on your needs
        }

        // Must be a JSON array
        if (!str_starts_with($trimmed, '[') || !str_ends_with($trimmed, ']')) {
            return false;
        }

        // Decode and validate
        $decoded = json_decode($trimmed, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        // Ensure it's an array (not null from decoding "null")
        if (!is_array($decoded)) {
            return false;
        }

        // Optional: Validate array contents
        return $this->validateArrayContents($decoded);
    }

    /**
     * Optional: Validate that array contains only specific types.
     */
    private function validateArrayContents(array $data): bool
    {
        // Example: Only allow arrays of strings or integers
        foreach ($data as $item) {
            if (!is_string($item) && !is_int($item) && !is_float($item) && !is_array($item)) {
                return false;
            }
        }

        return true;
    }
}