<?php

declare(strict_types=1);

class VideoUploadService extends AbstractFileUploadService
{
    private const string TARGET_DIR = STORAGE . 'uploads/videos/';
    private const string TEMP_DIR = STORAGE . 'uploads/temp/videos/';
    private const string WEB_PATH = '/uploads/videos/';

    public function getHandledUploadFileType(): UploadFileType
    {
        return UploadFileType::VIDEO;
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