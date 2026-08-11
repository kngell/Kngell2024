<?php

declare(strict_types=1);

class DocumentUploadService extends AbstractFileUploadService
{
    private const string TARGET_DIR = STORAGE . 'uploads/documents/';
    private const string TEMP_DIR = STORAGE . 'uploads/temp/documents/';
    private const string WEB_PATH = '/uploads/documents/';

    public function getHandledUploadFileType(): UploadFileType
    {
        return UploadFileType::DOCUMENT;
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