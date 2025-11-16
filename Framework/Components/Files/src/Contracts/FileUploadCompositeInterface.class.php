<?php

declare(strict_types=1);

interface FileUploadCompositeInterface extends FileUploadComponentInterface
{
    public function getFilePath(string $fieldName): ?string;

    public function getMultiFilePaths(string $fieldGallery): array;

    public function getSerializedMediaPaths(): ?string;
}