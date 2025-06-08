<?php

declare(strict_types=1);

/**
 * Configuration for validation behavior.
 */
final readonly class ValidationConfig
{
    public function __construct(
        private bool $stopOnFirstError = false,
        private bool $validateAllFields = true,
        private bool $sanitizeInput = false,
        private array $validationGroups = [],
        private bool $skipMissingFields = false
    ) {
    }

    public function shouldStopOnFirstError(): bool
    {
        return $this->stopOnFirstError;
    }

    public function shouldValidateAllFields(): bool
    {
        return $this->validateAllFields;
    }

    public function shouldSanitizeInput(): bool
    {
        return $this->sanitizeInput;
    }

    public function getValidationGroups(): array
    {
        return $this->validationGroups;
    }

    public function shouldSkipMissingFields(): bool
    {
        return $this->skipMissingFields;
    }

    public function hasValidationGroups(): bool
    {
        return ! empty($this->validationGroups);
    }

    public static function default(): self
    {
        return new self();
    }

    public static function stopOnFirstError(): self
    {
        return new self(stopOnFirstError: true);
    }

    public static function withGroups(array $groups): self
    {
        return new self(validationGroups: $groups);
    }
}