<?php

declare(strict_types=1);

class FileProcessorFactory
{
    private array $fileProcessors = [];

    public function __construct(
    ) {
        $this->initializeFileProcessors();
    }

    public function getFileProcessor(FileUpload $file): ?FileProcessorInterface
    {
        foreach ($this->fileProcessors as $processor) {
            if ($processor->supports($file)) {
                return $processor;
            }
        }
        return null;
    }

    private function initializeFileProcessors(): void
    {
        $this->fileProcessors = [
            new ImageProcessor(),
            new VideoProcessor(),
        ];
    }
}