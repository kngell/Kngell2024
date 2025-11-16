<?php

declare(strict_types=1);
interface FileUploadComponentInterface
{
    public function proceed(bool $uploadRequired = false, bool $temporary = false): void;

    public function getMediaPaths(): ?array;

    public function getErrors(): array;

    public function cleanup(): void;

    public function getUploadedFileInfo(): array;

    public function getUploadStats(): array;

    public function getFileInformationObjects(): array;

    public function getFileMetadata(): array;

    public function isTemporary(): bool;

    public function makePermanent(): bool;

    public function hasErrors(): bool;

    public function setFormTemporaryWebPaths(array $formTemporaryWebPaths): FileUploadComponentInterface;

    public function cleanupOldTempFiles(): int;

    public function getNbOfoldFilesCleanedUp(): int;

    public function cleanupPermanentFiles(): void;

    public function hasWebpaths(): bool;
}