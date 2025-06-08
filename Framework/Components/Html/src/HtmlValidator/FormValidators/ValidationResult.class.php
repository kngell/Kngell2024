<?php

declare(strict_types=1);

/**
 * Represents the result of a validation operation.
 */
final readonly class ValidationResult
{
    public function __construct(
        private array $errors = [],
        private array $validatedData = [],
        private bool $stopOnFirstError = false
    ) {
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorsFor(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    public function getFirstError(): ?string
    {
        if (empty($this->errors)) {
            return null;
        }

        $firstField = array_key_first($this->errors);
        $fieldErrors = $this->errors[$firstField];

        return is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors;
    }

    public function getFirstErrorFor(string $field): ?string
    {
        $fieldErrors = $this->getErrorsFor($field);
        return empty($fieldErrors) ? null : (is_array($fieldErrors) ? $fieldErrors[0] : $fieldErrors);
    }

    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    public function getValidatedValue(string $field): mixed
    {
        return $this->validatedData[$field] ?? null;
    }

    public function getErrorCount(): int
    {
        return array_sum(array_map('count', $this->errors));
    }

    public function getFieldsWithErrors(): array
    {
        return array_keys($this->errors);
    }

    public function toArray(): array
    {
        return [
            'valid' => $this->isValid(),
            'errors' => $this->errors,
            'validated_data' => $this->validatedData,
            'error_count' => $this->getErrorCount(),
        ];
    }
}