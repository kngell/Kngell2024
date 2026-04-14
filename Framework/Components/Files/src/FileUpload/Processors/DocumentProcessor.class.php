<?php

declare(strict_types=1);

class DocumentProcessor implements FileProcessorInterface
{
    public function supports(FileUpload $file): bool
    {
        return $file->isDocument();
    }

    public function process(FileUpload $source, string $targetPath): ?string
    {
        // For documents, we might want to:
        // - Scan for viruses
        // - Extract metadata
        // - Convert to different formats
        // - Add watermarks

        // For now, just return null to use regular file move
        return null;
    }
}
