<?php

declare(strict_types=1);

interface FileUploadComponentInterface
{
    /**
     * Process file upload(s) for this component.
     */
    public function proceed(bool $uploadRequired = false, bool $temporary = false): void;

    /**
     * Get all media paths that were uploaded/processed
     * Returns empty array if no files were uploaded.
     */
    public function getMediaPaths(): array;

    /**
     * Get all validation/upload errors
     * Returns array with field name as key and array of error messages as value.
     */
    public function getErrors(): array;

    /**
     * Clean up any uploaded files (delete them).
     */
    public function cleanup(): void;

    /**
     * Get metadata for uploaded files.
     */
    public function getUploadedFileInfo(): array;

    /**
     * Get statistics about the upload process.
     */
    public function getUploadStats(): array;

    /**
     * Get file information objects.
     */
    public function getFileInformationObjects(): array;

    /**
     * Get detailed file metadata.
     */
    public function getFileMetadata(): array;

    /**
     * Check if files are stored temporarily.
     */
    public function isTemporary(): bool;

    /**
     * Move temporary files to permanent storage
     * Returns true on success, false on failure.
     */
    public function makePermanent(): bool;

    /**
     * Check if there are any errors.
     */
    public function hasErrors(): bool;

    /**
     * Set temporary web paths from form data (for edit scenarios).
     */
    public function setFormTemporaryWebPaths(array $formTemporaryWebPaths): self;

    /**
     * Get temporary web paths.
     */
    public function getFormTemporaryWebPaths(): array;

    /**
     * Clean up old temporary files
     * Returns number of files cleaned up.
     */
    public function cleanupOldTempFiles(): int;

    /**
     * Get number of old files cleaned up in last cleanup.
     */
    public function getNbOfoldFilesCleanedUp(): int;

    /**
     * Clean up permanent files (delete them).
     */
    public function cleanupPermanentFiles(): void;

    /**
     * Check if there are web paths available.
     */
    public function hasWebpaths(): bool;

    /**
     * Get the field name this component handles.
     */
    public function getFieldName(): string;

    /**
     * Check if component has any files (uploaded or temporary).
     */
    public function hasFiles(): bool;
}