<?php

declare(strict_types=1);

class ArrayType implements TypeHandlerInterface
{
    public function supports(mixed $value, ?ReflectionProperty $property = null): bool
    {
        return is_array($value) ||
               (is_string($value) && $this->isJsonString($value));
    }

    public function normalizeForDatabase(mixed $value): mixed
    {
        if ($value === null || $value === []) {
            return null;
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException('Value must be an array for database normalization');
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function normalizeForEntity(mixed $value, ReflectionProperty $property, object $contextEntity): mixed
    {
        // SAFETY CHECK: If we receive an unsupported value, return empty array
        if (!$this->supports($value, $property)) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $array = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $array;
            }
            return [];
        }

        // This should never happen due to the safety check above
        return [];
    }

    private function isJsonString(string $value): bool
    {
        $trimmed = trim($value);
        if ($trimmed === '' || $trimmed === 'null') {
            return false;
        }

        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
}