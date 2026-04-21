<?php

declare(strict_types=1);

abstract class AbstractValidator
{
    public function __construct()
    {
    }

    abstract public function validate(): array|string|bool;

    protected function errorMessage(string $errMsg, array $classes = []): string
    {
        return FieldErrorHtml::format($errMsg, $classes);
    }

    protected function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed === '' || $trimmed === '[]' || $trimmed === '{}';
        }

        if (is_array($value)) {
            return empty($value);
        }

        // For countable objects
        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        // For objects with isEmpty method
        if (is_object($value) && method_exists($value, 'isEmpty')) {
            return $value->isEmpty();
        }

        // For stringable objects
        if (is_object($value) && method_exists($value, '__toString')) {
            return trim((string) $value) === '';
        }

        return false;
    }

    /**
     * Check if a value is not empty (inverse of isEmpty).
     */
    protected function isNotEmpty(mixed $value): bool
    {
        return !$this->isEmpty($value);
    }

    protected function trimString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return trim($value);
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return trim((string) $value);
        }

        return (string) $value;
    }

    protected function buildErrorMessage(array $errorParams): array
    {
        $message = $errorParams['message'] ?? '%s must be unique';
        $classes = $errorParams['classes'] ?? ['text-danger', 'validation-error'];

        return [$message, $classes];
    }

    protected function extractFieldName(string $path): string
    {
        if (preg_match('/\[([^\]]+)\]$/', $path, $matches)) {
            return $matches[1];
        }
        return $path;
    }

    protected function resolveFieldValue(string $targetField, array $formData, string $fieldName): mixed
    {
        if (!$formData) {
            return null;
        }

        if (str_contains($fieldName, '[')) {
            preg_match_all('/[^[\]]+/', $fieldName, $matches);
            $keys = $matches[0];
            array_pop($keys); // Remove current field

            $cursor = $formData;
            foreach ($keys as $key) {
                if (isset($cursor[$key])) {
                    $cursor = $cursor[$key];
                } else {
                    $cursor = null;
                    break;
                }
            }

            if ($cursor && isset($cursor[$targetField])) {
                return $cursor[$targetField];
            }
        }

        return $formData[$targetField] ?? null;
    }

    protected function convertToString(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }
}