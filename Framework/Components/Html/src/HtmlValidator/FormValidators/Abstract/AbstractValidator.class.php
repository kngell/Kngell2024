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
        $classString = !empty($classes) ? " class='" . implode(' ', $classes) . "'" : '';
        return "<small{$classString}>{$errMsg}</small>";
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
}