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

    public function getHandledUploadFileType(): UploadFileType
    {
        return UploadFileType::DOCUMENT;
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
