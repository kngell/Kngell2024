<?php

declare(strict_types=1);

class DeletionValidatorResult
{
    private array $errors = [];
    private array $warnings = [];
    private ?object $record = null;
    private ?string $displayName = null;
    private ?string $displayImage = null;
    private bool $softDeleted = false;
    private array $metadata = [];

    // --- Errors ---

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrorMessage(): ?string
    {
        return $this->errors[0] ?? null;
    }

    public function getValidationDetails(): array
    {
        return ['errors' => $this->errors];
    }

    // --- Warnings ---

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    // --- Record ---

    public function setRecord(object $record): void
    {
        $this->record = $record;
    }

    public function getRecord(): ?object
    {
        return $this->record;
    }

    // --- Display ---

    public function setDisplayName(string $name): void
    {
        $this->displayName = $name;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayImage(?string $image): void
    {
        $this->displayImage = $image;
    }

    public function getDisplayImage(): ?string
    {
        return $this->displayImage;
    }

    // --- Soft delete state ---

    public function setSoftDeleted(bool $deleted): void
    {
        $this->softDeleted = $deleted;
    }

    public function isSoftDeleted(): bool
    {
        return $this->softDeleted;
    }

    // --- Metadata ---

    public function setMetadata(string $key, mixed $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function getMetadata(string $key): mixed
    {
        return $this->metadata[$key] ?? null;
    }

    public function getAllMetadata(): array
    {
        return $this->metadata;
    }
}