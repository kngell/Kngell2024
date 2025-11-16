<?php

declare(strict_types=1);

class DocumentProcessor implements FileProcessorInterface
{
    private const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'txt'];

    public function supports(FileUpload $file): bool
    {
        $extension = $file->getOriginalExtension();
        return in_array($extension, self::ALLOWED_EXTENSIONS);
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