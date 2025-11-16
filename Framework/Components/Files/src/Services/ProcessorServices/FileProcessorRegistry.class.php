<?php

declare(strict_types=1);

class FileProcessorRegistry
{
    public function __construct(
        private ImageProcessor $imageProcessor,
        private DocumentProcessor $documentProcessor,
        private VideoProcessor $videoProcessor,
    ) {
    }

    public function getProcessor(UploadFileType $fileType): object
    {
        return match($fileType) {
            UploadFileType::IMAGE => $this->imageProcessor,
            UploadFileType::DOCUMENT => $this->documentProcessor,
            UploadFileType::VIDEO => $this->videoProcessor,
            default => $this->documentProcessor, // fallback
        };
    }
}
