<?php

declare(strict_types=1);
interface FileSearchInterface
{
    public function findFile(string $directory, string $filename, ?string $inDirectoryPath = null): ?FileInformation;

    public function findViewFile(string $viewsDirectory, string $viewPath): FileInformation;

    public function findFilesByPattern(string $directory, string $pattern): array;

    public function findFilesByExtension(string $directory, string $extension): array;

    public function findFilesByMimeType(string $directory, string $mimeType): array;

    public function getAllFiles(string $directory, ?string $extension = null): array;

    public function getAllAvailableViews(string $viewsDirectory): array;
}