<?php

declare(strict_types=1);
class AudioUploadService extends AbstractFileUploadService
{
    // public function __construct(
    //     private AudioProcessor $audioProcessor,
    //     FileMoverInterface $fileMover,
    //     FileMetadataService $metadataService,
    //     Request $request,
    //     ?string $fieldName = null,
    // ) {
    //     parent::__construct($fileMover, $request, $fieldName);
    // }

    public function getStorageBaseDirectory(): string
    {
        throw new Exception('Not implemented');
    }

    public function getWebBasePath(): string
    {
        throw new Exception('Not implemented');
    }

    public function getHandledUploadFileType(): UploadFileType
    {
        return UploadFileType::AUDIO;
    }

    protected function getTempDirectory(): string
    {
        throw new Exception('Not implemented');
    }

    // protected function getProcessor(): object
    // {
    //     return $this->audioProcessor;
    // }

    protected function getTargetDirectory(): string
    {
        return SRC . 'Upload' . DS . 'audio' . DS;
    }
}