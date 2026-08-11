<?php

declare(strict_types=1);

final class SaveResult
{
    private function __construct(
        public readonly OperationStatus $status,
        public readonly string $entityName,
        public readonly ?int $entityId = null,
        public readonly string $operationType = '',
        public readonly bool $anythingChanged = false,
        public readonly bool $wasSkipped = false,
        public readonly string $message = '',
        public readonly string $redirectUrl = '',
        public readonly array $errors = [],
        public readonly array $extraData = [],
        public readonly HttpStatusCode $statusCode = HttpStatusCode::HTTP_OK,
        public readonly ?Throwable $exception = null,
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->status === OperationStatus::SUCCESS;
    }

    public static function success(
        string $entityName,
        int $entityId,
        string $operationType,
        bool $anythingChanged,
        bool $wasSkipped,
        string $message,
        string $redirectUrl,
        array $extraData = [],
    ): self {
        return new self(
            status: OperationStatus::SUCCESS,
            entityName: $entityName,
            entityId: $entityId,
            operationType: $operationType,
            anythingChanged: $anythingChanged,
            wasSkipped: $wasSkipped,
            message: $message,
            redirectUrl: $redirectUrl,
            extraData: $extraData,
        );
    }

    public static function noData(
        string $entityName,
        string $message,
        string $redirectUrl,
    ): self {
        return new self(
            status: OperationStatus::NO_DATA,
            entityName: $entityName,
            message: $message,
            redirectUrl: $redirectUrl,
        );
    }

    public static function validationError(
        string $entityName,
        array $errors,
        string $redirectUrl,
    ): self {
        return new self(
            status: OperationStatus::VALIDATION_ERROR,
            entityName: $entityName,
            message: 'The form contains one or many errors.',
            redirectUrl: $redirectUrl,
            errors: $errors,
            statusCode: HttpStatusCode::HTTP_UNPROCESSABLE_ENTITY,
        );
    }

    public static function fileStorageError(
        string $entityName,
        string $redirectUrl,
    ): self {
        return new self(
            status: OperationStatus::FILE_STORAGE_ERROR,
            entityName: $entityName,
            message: 'Failed to save files permanently.',
            redirectUrl: $redirectUrl,
            statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

    public static function databaseError(
        string $entityName,
        string $redirectUrl,
        Throwable $exception,
        array $extraData = [],
    ): self {
        return new self(
            status: OperationStatus::DATABASE_ERROR,
            entityName: $entityName,
            message: 'A database error occurred while saving.',
            redirectUrl: $redirectUrl,
            extraData: $extraData,
            statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            exception: $exception,
        );
    }
}