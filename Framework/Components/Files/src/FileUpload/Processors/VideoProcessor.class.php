<?php

declare(strict_types=1);
class VideoProcessor implements FileProcessorInterface
{
    public function process(FileUpload $source, string $targetPath): ?string
    {
        throw new Exception('Not implemented');
    }

    public function supports(FileUpload $file): bool
    {
        return $file->isVideo();
    }
}
