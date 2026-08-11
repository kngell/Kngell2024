<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class FileUploadFactory
{
    use FileTrimTrait;

    public function __construct(
        private FileMoverInterface $fileMover,
        private FileMetadataService $metadataService,
        private FileProcessorFactory $processorFactory,
        private PathResolver $pathResolver,
        private TempFileCleaner $tempFileCleaner,
        private LoggerInterface $logger,
    ) {
    }

    public function create(
        Request $request,
        array $fieldErrors = [],
        array $webPaths = [],
    ): FileUploadCompositeInterface {
        $uploadService = new UploadService($this->tempFileCleaner, $fieldErrors);

        $fileFields = $request->getFiles()->getFieldNames();
        $webPaths = ArrayUtils::flattenWithKeys($webPaths);
        $webPathFields = array_keys($webPaths);

        $allFields = array_unique(array_merge($fileFields, $webPathFields));

        $this->logger->debug('Creating upload components for fields:', $allFields);

        foreach ($allFields as $fieldName) {
            if ($this->fieldHasValidationErrors($fieldName, $fieldErrors)) {
                $this->logger->debug("Skipping field {$fieldName} due to validation errors");
                continue;
            }

            $component = $this->createComponent($request, $fieldName, $webPaths);
            if ($component === null) {
                $this->logger->warning("Could not create component for field: {$fieldName}");
                continue;
            }

            // Set preserved web paths for this field
            if (isset($webPaths[$fieldName])) {
                $fieldWebPaths = $this->normalizeWebPaths($webPaths[$fieldName]);
                if (!empty($fieldWebPaths)) {
                    $this->logger->debug("Setting web paths for {$fieldName}:", $fieldWebPaths);
                    $component->setFormTemporaryWebPaths($fieldWebPaths);
                }
            }

            $uploadService->add($fieldName, $component);
        }

        return $uploadService;
    }

    private function normalizeWebPaths(mixed $paths): array
    {
        if (empty($paths)) {
            return [];
        }

        if (is_string($paths)) {
            return [$paths];
        }

        if (is_array($paths)) {
            $result = [];
            array_walk_recursive($paths, function ($value) use (&$result) {
                if (is_string($value) && !empty($value)) {
                    $result[] = $value;
                }
            });
            return $result;
        }

        return [];
    }

    private function createComponent(Request $request, string $fieldName, array $webPaths = []): ?FileUploadComponentInterface
    {
        $serviceClass = $this->determineServiceClass($fieldName, $request, $webPaths);

        if (!$serviceClass) {
            $this->logger->warning('No service class found for field', ['field' => $fieldName]);
            return null;
        }

        return $this->instantiateService($serviceClass, $fieldName, $request);
    }

    private function determineServiceClass(string $fieldName, Request $request, array $webPaths = []): ?string
    {
        // Priority 1: Check uploaded files in request
        $files = $request->getFiles();

        // Try different name patterns
        $keysToTry = [$fieldName, $fieldName . '[0]', $fieldName . '[]', $fieldName . '[url]'];

        foreach ($keysToTry as $key) {
            $fileData = $files->getFile($key);

            if (!$fileData) {
                continue;
            }

            // Handle array of files (multiple uploads)
            if (is_array($fileData)) {
                foreach ($fileData as $file) {
                    if ($file instanceof FileUpload && $file->getError()->isSuccess()) {
                        $serviceClass = $this->getUploadService($file);
                        if ($serviceClass) {
                            return $serviceClass;
                        }
                    }
                }
            }
            // Handle single file
            elseif ($fileData instanceof FileUpload && $fileData->getError()->isSuccess()) {
                $serviceClass = $this->getUploadService($fileData);
                if ($serviceClass) {
                    return $serviceClass;
                }
            }
        }

        // Priority 2: Check existing web paths
        if (!empty($webPaths)) {
            // Extract all string paths from nested structure
            $webPathsArray = $this->getWebPathArray($webPaths);

            if (!empty($webPathsArray)) {
                $serviceClass = $this->guessServiceFromWebPaths($webPathsArray);
                if ($serviceClass) {
                    return $serviceClass;
                }
            }
        }

        // Priority 3: Fallback to field name guessing
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

    private function getWebPathArray(array $webPaths, int $depth = 0): array
    {
        // Prevent infinite recursion
        if ($depth > 10) {
            return [];
        }

        $result = [];

        foreach ($webPaths as $key => $value) {
            if (is_array($value)) {
                // Recursively extract from nested arrays
                $nested = $this->getWebPathArray($value, $depth + 1);
                $result = array_merge($result, $nested);
            } elseif (is_string($value) && !empty($value)) {
                // Valid string path
                $result[] = $value;
            }
        }

        return $result;
    }

    private function guessServiceFromWebPaths(array $webPaths): ?string
    {
        if (empty($webPaths)) {
            return null;
        }

        foreach ($webPaths as $file) {
            if (!is_string($file) || empty($file)) {
                continue;
            }

            try {
                $fileInfo = new FileInformation($file);

                // Return first matching service
                if ($fileInfo->isImage()) {
                    return ImageUploadService::class;
                }
                if ($fileInfo->isVideo()) {
                    return VideoUploadService::class;
                }
                if ($fileInfo->isAudio()) {
                    return AudioUploadService::class;
                }
                if ($fileInfo->isDocument()) {
                    return DocumentUploadService::class;
                }
            } catch (Exception $e) {
                // Log error but continue checking other files
                continue;
            }
        }

        return null;
    }

    private function guessServiceFromFieldName(string $fieldName): ?string
    {
        $lowerField = strtolower($fieldName);

        if (preg_match('/image|img|photo|picture|avatar|logo/i', $lowerField)) {
            return ImageUploadService::class;
        }
        if (preg_match('/video|movie|clip/i', $lowerField)) {
            return VideoUploadService::class;
        }
        if (preg_match('/audio|sound|music|podcast/i', $lowerField)) {
            return AudioUploadService::class;
        }

        return DocumentUploadService::class;
    }

    private function instantiateService(
        string $serviceClass,
        string $fieldName,
        Request $request,
    ): FileUploadComponentInterface {
        $services = [
            ImageUploadService::class => ImageUploadService::class,
            VideoUploadService::class => VideoUploadService::class,
            AudioUploadService::class => AudioUploadService::class,
            DocumentUploadService::class => DocumentUploadService::class,
        ];

        if (!isset($services[$serviceClass])) {
            throw new InvalidArgumentException("Unknown service class: $serviceClass");
        }

        return new $serviceClass(
            $this->processorFactory,
            $this->fileMover,
            $this->metadataService,
            $this->pathResolver,
            $this->tempFileCleaner,
            $request,
            $fieldName,
        );
    }

    private function fieldHasValidationErrors(string $fieldName, array $fieldErrors): bool
    {
        $cleanField = rtrim($fieldName, '[]');
        $baseField = $this->getBaseFieldName($fieldName);

        return isset($fieldErrors[$fieldName])
            || isset($fieldErrors[$cleanField])
            || isset($fieldErrors[$baseField]);
    }
}