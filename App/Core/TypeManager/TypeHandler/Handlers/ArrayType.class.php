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
            return $this->isJsonString($value);
        }

        return false;
    }

    public function normalizeForDatabase(mixed $value, ?ReflectionProperty $property = null): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            if ($this->isJsonString($value)) {
                return $value;
            }

            throw new InvalidArgumentException(sprintf(
                'String value passed to ArrayType is not a valid JSON structure. Got: "%s"',
                $value,
            ));
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException(sprintf(
                'Value must be an array or JSON string for database normalization. Got: %s',
                gettype($value),
            ));
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, object $contextEntity): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

                if (is_array($decoded)) {
                    return $decoded;
                }
            } catch (JsonException $e) {
                // Fail silently and fallback to safe array format on extraction error
                return [];
            }
        }

        return [];
    }

    /**
     * Verifies if a string is a valid JSON Array OR a valid JSON Object.
     */
    private function isJsonString(string $value): bool
    {
        $trimmed = trim($value);

        if ($trimmed === '' || $trimmed === 'null') {
            return false;
        }

        // Must start/end with brackets [] OR curly braces {}
        $isWrapped = (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) ||
                     (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}'));

        if (!$isWrapped) {
            return false;
        }

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded);
        } catch (JsonException $e) {
            return false;
        }
    }
}