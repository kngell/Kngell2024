<?php

declare(strict_types=1);

class ImageUploadService extends AbstractFileUploadService
{
    private const string UPLOAD_DIR = STORAGE . 'uploads' . DS . 'images' . DS;
    private const string UPLOAD_TEMP_DIR = STORAGE . 'uploads' . DS . 'temp' . DS . 'images' . DS;

    public function __construct(
        private ImageProcessor $imageProcessor,
        FileMoverService $fileMover,
        FileMetadataService $metadataService,
        Request $request,
        ?string $fieldName = null,
    ) {
        parent::__construct($fileMover, $metadataService, $request, $fieldName);
    }

    public function getHandledUploadFileType(): UploadFileType
    {
        return UploadFileType::IMAGE;
    }

    public function getWebBasePath(): string
    {
        return '/uploads/images/';
    }

    // Remove these if not needed in abstract class
    public function getStorageBaseDirectory(): string
    {
        return STORAGE;
    }

    protected function getProcessor(): object
    {
        return $this->imageProcessor;
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