<?php

declare(strict_types=1);

interface FormDataHandlerInterface
{
    public function storeFormData(
        array $formData,
        UploadService $upload,
        array $errors,
        array $webPaths,
        string $requestUri,
    ): void;

    public function prepareForValidation(array $data): array;

    public function extractWebPathsFromForm(array &$formData): array;

    public function prepareFormDataForView(array $formData, array $fileMetadata): array;

    public function getStoredFormData(string $requestUri): array;

    public function clearStoredFormData(string $requestUri): void;

    public function validateWebPaths(array $webPaths): array;

    public function getMetadataService(): FileMetadataService;

    public function isEmptyData(array $data, array $additionalExcludeKeys = []): bool;
}