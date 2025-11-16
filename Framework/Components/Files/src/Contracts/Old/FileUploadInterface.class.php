<?php

declare(strict_types=1);

interface FileUploadInterface
{
    public function proceed(bool $uploadRequired = false): void;

    public function getMediaPaths(): string|null;

    public function getErrors(): array;

    public function cleanup(): void;

    public function getUploadedFileInfo(): array;

    public function getUploadStats(): array;

    public function getFileInformationObjects(): array;

    public function getFileMetadata(): array;

    public function isTemporary(): bool;

    public function makePermanent(): bool;

    public function hasErrors(): bool;

    public function setFormTemporaryWebPaths(array $formTemporaryWebPaths): ImageUploadService;
}