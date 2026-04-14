<?php

declare(strict_types=1);

interface FileMoverInterface
{
    public function moveUploadedFile(
        FileUpload $upload,
        string $targetDirectory,
        ?string $filename = null,
        ?FileProcessorInterface $processor = null,
    ): FileInformation;

    public function moveFile(
        FileInformation $source,
        string $targetDirectory,
        ?string $filename = null,
    ): FileInformation;

    public function makeFilePermanent(string $tempPath, string $permanentDirectory): string;

    public function makeFilesPermanent(array $tempPaths, string $permanentDirectory): array;

    public function deletePermanentFile(string $path): void;
}