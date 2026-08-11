<?php

declare(strict_types=1);

class AudioUploadService extends AbstractFileUploadService
{
    private const string TARGET_DIR = STORAGE . 'uploads/audio/';
    private const string TEMP_DIR = STORAGE . 'uploads/temp/audio/';
    private const string WEB_PATH = '/uploads/audio/';

    public function getHandledUploadFileType(): UploadFileType
    {
        return UploadFileType::AUDIO;
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