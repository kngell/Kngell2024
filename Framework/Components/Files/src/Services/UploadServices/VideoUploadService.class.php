<?php

declare(strict_types=1);
class VideoUploadService extends AbstractFileUploadService
{
    private const string UPLOAD_DIR = STORAGE . 'uploads' . DS . 'videos';
    private const string UPLOAD_TEMP_DIR = STORAGE . 'uploads' . DS . 'temp' . DS . 'videos';

    public function __construct(
        private VideoProcessor $videoProcessor,
        FileMoverService $fileMover,
        Request $request,
        ?string $fieldName = null,
    ) {
        parent::__construct($fileMover, $request, $fieldName);
    }

    public function getStorageBaseDirectory(
    ): string {
        return STORAGE;
    }

    public function getWebBasePath(): string
    {
        return DS . 'uploads' . DS . 'videos' . DS;
    }

    public function initializeDirectories(): void
    {
    }

    public function getHandledUploadFileType(): UploadFileType
    {
        return UploadFileType::VIDEO;
    }

    protected function getProcessor(): object
    {
        return $this->videoProcessor;
    }

    protected function getTargetDirectory(): string
    {
        return self::UPLOAD_DIR;
    }

    protected function getTempDirectory(): string
    {
        return self::UPLOAD_TEMP_DIR;
    }
}