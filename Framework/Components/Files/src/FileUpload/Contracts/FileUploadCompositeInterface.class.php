<?php

declare(strict_types=1);

interface FileUploadCompositeInterface extends FileUploadComponentInterface
{
    /**
     * Add a component for a specific field.
     */
    public function add(string $fieldName, FileUploadComponentInterface $component): void;

    /**
     * Remove a component by field name.
     */
    public function remove(string $fieldName): void;

    /**
     * Get a component by field name.
     */
    public function get(string $fieldName): ?FileUploadComponentInterface;

    /**
     * Get all components.
     */
    public function all(): array;

    /**
     * Get file path for a specific field.
     */
    public function getFilePath(string $fieldName): ?string;

    /**
     * Get multiple file paths for a field (for galleries/multi-uploads).
     */
    public function getMultiFilePaths(string $fieldName): array;

    /**
     * Get file information grouped by field.
     */
    public function getFileInformationByField(): array;

    /**
     * Get file metadata grouped by field.
     */
    public function getFileMetadataByField(): array;

    /**
     * Check if a specific field has errors.
     */
    public function hasFieldErrors(string $fieldName): bool;

    /**
     * Get errors for a specific field.
     */
    public function getFieldErrors(string $fieldName): array;

    /**
     * Get media paths for a specific field.
     */
    public function getFieldMediaPaths(string $fieldName): ?array;

    /**
     * Get total number of files across all components.
     */
    public function getTotalFileCount(): int;

    /**
     * Get file count per field.
     */
    public function getFileCountByField(): array;
}