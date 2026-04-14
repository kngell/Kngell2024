<?php

declare(strict_types=1);

class DeleteResult
{
    private bool $success;
    private array $data;
    private ?string $errorMessage;
    private array $errorDetails;

    private function __construct(
        bool $success,
        array $data = [],
        ?string $errorMessage = null,
        array $errorDetails = [],
    ) {
        $this->success = $success;
        $this->data = $data;
        $this->errorMessage = $errorMessage;
        $this->errorDetails = $errorDetails;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }

    // Convenience methods
    public function getProductId(): ?string
    {
        return $this->data['product_id'] ?? null;
    }

    public function getProductName(): ?string
    {
        return $this->data['product_name'] ?? null;
    }

    public function wasSoftDeleted(): bool
    {
        return $this->data['is_soft_deleted'] ?? false;
    }

    public function wasSkipped(): bool
    {
        return $this->data['was_skipped'] ?? false;
    }

    public function getSkipReason(): string
    {
        return $this->data['skip_reason'] ?? '';
    }

    public function getProductEntity(): ?object
    {
        return $this->data['product_entity'] ?? null;
    }

    public function getAffectedRows(): int
    {
        return $this->data['affected_rows'] ?? 0;
    }

    // Helper to check if operation actually changed anything
    public function hasChanges(): bool
    {
        return !$this->wasSkipped() && $this->getAffectedRows() > 0;
    }

    public static function success(array $data = []): self
    {
        return new self(true, $data);
    }

    public static function failure(string $message, array $details = []): self
    {
        return new self(false, [], $message, $details);
    }
}