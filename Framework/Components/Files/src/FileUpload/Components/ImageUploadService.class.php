<?php

declare(strict_types=1);

class ImageUploadService extends AbstractFileUploadService
{
    private const string TARGET_DIR = STORAGE . 'uploads/images/';
    private const string TEMP_DIR = STORAGE . 'uploads/images/temp/';
    private const string WEB_PATH = '/uploads/images/';

    public function getHandledUploadFileType(): UploadFileType
    {
        return UploadFileType::IMAGE;
    }

    public function getWebBasePath(): string
    {
        return self::WEB_PATH;
    }

    protected function getTargetDirectory(): string
    {
        return self::TARGET_DIR;
    }

    protected function getTempDirectory(): string
    {
        return self::TEMP_DIR;
    }
}