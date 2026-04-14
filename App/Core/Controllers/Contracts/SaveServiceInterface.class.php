<?php

declare(strict_types=1);

interface SaveServiceInterface
{
    public function getValidationRules(): string;

    public function getModel(): Model;

    public function getEntityName(): string;

    public function getEventClass(): string;

    public function processFilePaths(array $formData, FileUploadCompositeInterface $uploadService): array;

    public function buildEventData(
        array $formData,
        array $filePaths,
        string $operationType,
        int $entityId,
        bool $wasSkipped,
        array $modelData = [],
    ): array;

    public function getRedirectUrl(?int $entityId = null, string $operationType = ''): string;

    public function getSuccessMessage(string $operationType, bool $wasSkipped): string;

    public function getEntityIdFromForm(array $formData): ?int;
}