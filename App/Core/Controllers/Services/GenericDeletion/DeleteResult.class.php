<?php

declare(strict_types=1);

class DeleteResult
{
    private function __construct(
        private bool $success,
        private array $id,
        private ?string $name,
        private ?string $errorMessage,
        private array $errorDetails,
        private int $affectedRows,
        private bool $wasSkipped,
        private string $skipReason,
        private bool $isSoftDeleted,
        private string $deletionType,
        private array $warnings,
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getId(): array
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getErrorDetails(): array
    {
        return $this->errorDetails;
    }

    public function getAffectedRows(): int
    {
        return $this->affectedRows;
    }

    public function wasSkipped(): bool
    {
        return $this->wasSkipped;
    }

    public function getSkipReason(): string
    {
        return $this->skipReason;
    }

    public function isSoftDeleted(): bool
    {
        return $this->isSoftDeleted;
    }

    public function getDeletionType(): string
    {
        return $this->deletionType;
    }

    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public static function success(
        array $id,
        string $name,
        int $affectedRows = 0,
        bool $wasSkipped = false,
        string $skipReason = '',
        bool $isSoftDeleted = false,
        string $deletionType = 'archive',
        array $warnings = [],
    ): self {
        return new self(
            success: true,
            id: $id,
            name: $name,
            errorMessage: null,
            errorDetails: [],
            affectedRows: $affectedRows,
            wasSkipped: $wasSkipped,
            skipReason: $skipReason,
            isSoftDeleted: $isSoftDeleted,
            deletionType: $deletionType,
            warnings: $warnings,
        );
    }

    public static function failure(
        string $errorMessage,
        array $errorDetails = [],
    ): self {
        return new self(
            success: false,
            id: [],
            name: null,
            errorMessage: $errorMessage,
            errorDetails: $errorDetails,
            affectedRows: 0,
            wasSkipped: false,
            skipReason: '',
            isSoftDeleted: false,
            deletionType: '',
            warnings: [],
        );
    }
}