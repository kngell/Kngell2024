<?php

declare(strict_types=1);

interface FileProcessorInterface
{
    public function supports(FileUpload $file): bool;

    public function process(FileUpload $source, string $targetPath): ?string;
}