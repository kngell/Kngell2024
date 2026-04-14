<?php

declare(strict_types=1);

class FileUploadFactory
{
    public function __construct(
        private FileMoverInterface $fileMover,
        private FileMetadataService $metadataService,
        private FileProcessorFactory $processorFactory,
        private PathResolver $pathResolver,
        private TempFileCleaner $tempFileCleaner,
    ) {
    }

    public function create(
        Request $request,
        array $fieldErrors = [],
        array $webPaths = [],
    ): FileUploadCompositeInterface {
        $uploadService = new UploadService($fieldErrors);
        $files = $request->getFiles()->getFieldNames();
        $files = array_merge($files, $this->getFieldNames($webPaths));

        foreach ($files as $fieldName) {
            if ($this->fieldHasValidationErrors($fieldName, $fieldErrors)) {
                continue;
            }

            $component = $this->createComponent($request, $fieldName);
            $uploadService->add($fieldName, $component);
        }

        return $uploadService;
    }

    private function getFieldNames(array $webPaths): array
    {
        return array_keys($webPaths);
    }

    private function createComponent(
        Request $request,
        string $fieldName,
    ): FileUploadComponentInterface {
        $serviceClass = $this->determineServiceClass($fieldName, $request);
        return $this->instantiateService($serviceClass, $fieldName, $request);
    }

    private function determineServiceClass(string $fieldName, Request $request): string
    {
        $files = $request->getFiles();
        $fileData = $files->getFile($fieldName);

        if ($fileData !== null) {
            $filesArray = is_array($fileData) ? $fileData : [$fileData];

            foreach ($filesArray as $file) {
                if ($file instanceof FileUpload && $file->getError()->isSuccess()) {
                    return $this->getUploadService($file);
                    // $fileType = UploadFileType::fromFileUpload($file);
                    // return $this->mapFileTypeToService($fileType);
                }
            }
        }

        return $this->guessServiceFromFieldName($fieldName);
    }

    private function getUploadService(FileUpload $file): string
    {
        return match(true) {
            $file->isImage() => ImageUploadService::class,
            $file->isVideo() => VideoUploadService::class,
            $file->isAudio() => AudioUploadService::class,
            default => DocumentUploadService::class,
        };
    }

    private function guessServiceFromFieldName(string $fieldName): string
    {
        $lowerField = strtolower($fieldName);

        if (preg_match('/image|img|photo|picture|avatar|logo/i', $lowerField)) {
            return ImageUploadService::class;
        }

        // Video patterns (simplified)
        if (preg_match('/video|movie|clip/i', $lowerField)) {
            return VideoUploadService::class;
        }

        // Audio patterns (simplified)
        if (preg_match('/audio|sound|music|podcast/i', $lowerField)) {
            return AudioUploadService::class;
        }

        return DocumentUploadService::class;
    }

    /**
     * Instantiate service with dependencies.
     */
    private function instantiateService(
        string $serviceClass,
        string $fieldName,
        Request $request,
    ): FileUploadComponentInterface {
        return match($serviceClass) {
            ImageUploadService::class => new ImageUploadService(
                $this->processorFactory,
                $this->fileMover,
                $this->metadataService,
                $this->pathResolver,
                $this->tempFileCleaner,
                $request,
                $fieldName,
            ),
            VideoUploadService::class => new VideoUploadService(
                $this->processorFactory,
                $this->fileMover,
                $this->metadataService,
                $this->pathResolver,
                $this->tempFileCleaner,
                $request,
                $fieldName,
            ),
            AudioUploadService::class => new AudioUploadService(
                $this->processorFactory,
                $this->fileMover,
                $this->metadataService,
                $this->pathResolver,
                $this->tempFileCleaner,
                $request,
                $fieldName,
            ),
            // DocumentUploadService::class => new DocumentUploadService(
            //     $this->processorRegistry->getProcessor(UploadFileType::DOCUMENT),
            //     $this->fileMover,
            //     $this->metadataService,
            //     $request,
            //     $fieldName,
            // ),
            default => throw new InvalidArgumentException("Unknown service class: $serviceClass"),
        };
    }

    /**
     * Check if field has validation errors.
     */
    private function fieldHasValidationErrors(string $fieldName, array $fieldErrors): bool
    {
        $cleanFieldName = rtrim($fieldName, '[]');
        return isset($fieldErrors[$fieldName]) || isset($fieldErrors[$cleanFieldName]);
    }
}