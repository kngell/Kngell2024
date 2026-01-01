<?php

declare(strict_types=1);
class DocumentUploadService extends AbstractFileUploadService
{
    public function __construct(
        private DocumentProcessor $documentProcessor,
        FileMoverService $fileMover,
        Request $request,
        ?string $fieldName = null,
    ) {
        parent::__construct($fileMover, $request, $fieldName);
    }

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
        return UploadFileType::DOCUMENT;
    }

    protected function getTempDirectory(): string
    {
        throw new Exception('Not implemented');
    }

    protected function getProcessor(): object
    {
        return $this->documentProcessor;
    }

    protected function getTargetDirectory(): string
    {
        return SRC . 'Upload' . DS . 'documents' . DS;
    }
}