<?php

declare(strict_types=1);
class AudioUploadService extends AbstractFileUploadService
{
    public function __construct(
        private AudioProcessor $audioProcessor,
        FileMoverService $fileMover,
        Request $request,
        ?string $fieldName = null,
    ) {
        parent::__construct($fileMover, $request, $fieldName);
    }

    public function getHandledUploadFileType(): UploadFileType
    {
        return UploadFileType::AUDIO;
    }

    protected function getProcessor(): object
    {
        return $this->audioProcessor;
    }

    protected function getTargetDirectory(): string
    {
        return SRC . 'Upload' . DS . 'audio' . DS;
    }
}
