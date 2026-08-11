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

    public function all(): array;

    public function getFilePath(string $fieldName): null|string|array;

    public function getMultiFilePaths(string $fieldName): array;

    public function getFileInformationByField(): array;

    public function getMediaPathsByField(): array;

    public function getFileMetadataByField(): array;

    public function hasFieldErrors(string $fieldName): bool;

    public function getFieldErrors(string $fieldName): array;

    public function getFieldMediaPaths(string $fieldName): ?array;

    public function getMetadata(): array;

    public function getTotalFileCount(): int;

    public function getFileCountByField(): array;
}