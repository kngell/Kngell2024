<?php

declare(strict_types=1);
class UploadService implements FileUploadCompositeInterface
{
    private array $components = [];
    private array $errors = [];
    private array $formTemporaryWebPaths = [];
    private int $nbOfoldFilesCleanedUp = 0;

    public function __construct(
        private ?FileProcessorRegistry $processor,
        private ?FileMoverService $fileMover,
        private ?FileMetadataService $metadataService,
        private ?Request $request,
        private array $fieldsErrors,
    ) {
    }

    public function hasWebpaths(): bool
    {
        return !empty($this->formTemporaryWebPaths);
    }

    public function add(string $fieldName, FileUploadComponentInterface $component): void
    {
        $this->components[$fieldName] = $component;
    }

    public function remove(string $fieldName): void
    {
        unset($this->components[$fieldName]);
    }

    public function get(string $fieldName): ?FileUploadComponentInterface
    {
        return $this->components[$fieldName] ?? null;
    }

    public function all(): array
    {
        return $this->components;
    }

    public function proceed(bool $uploadRequired = false, bool $temporary = false): void
    {
        $this->errors = [];

        foreach ($this->components as $fieldName => $component) {
            $fieldHasErrors = $this->fieldHasValidationErrors($fieldName);

            if ($fieldHasErrors) {
                continue;
            }

            $component->proceed($uploadRequired, $temporary);

            // Aggregate errors
            $componentErrors = $component->getErrors();
            if (!empty($componentErrors)) {
                $this->errors[$fieldName] = $componentErrors;
            } else {
            }
        }
    }

    public function getMediaPaths(): ?array
    {
        $allPaths = [];
        foreach ($this->components as $component) {
            $paths = $component->getMediaPaths();

            if (is_array($paths) && !empty($paths)) {
                $allPaths = array_merge($allPaths, $paths);
            }
        }
        return $allPaths ?: null;
    }

    public function getFilePath(string $fieldName): ?string
    {
        $component = $this->get($fieldName);

        if (!$component) {
            return null;
        }
        $paths = $component->getMediaPaths();
        return !empty($paths) ? array_shift($paths) : null;
    }

    public function getMultiFilePaths(string $fieldGallery): array
    {
        $component = $this->get($fieldGallery);

        if (!$component) {
            return [];
        }
        $paths = $component->getMediaPaths();

        return $paths ?? [];
    }

    public function cleanupPermanentFiles(): void
    {
        foreach ($this->components as $component) {
            $component->cleanupPermanentFiles();
        }
    }

    public function getSerializedMediaPaths(): ?string
    {
        $allPaths = $this->getMediaPaths();
        return $allPaths ? serialize($allPaths) : null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function cleanup(): void
    {
        foreach ($this->components as $component) {
            $component->cleanup();
        }
    }

    public function getUploadedFileInfo(): array
    {
        $allFileInfo = [];

        foreach ($this->components as $fieldName => $component) {
            $componentFileInfo = $component->getUploadedFileInfo();
            if (!empty($componentFileInfo)) {
                $allFileInfo = array_merge($allFileInfo, $componentFileInfo);
            }
        }

        return $allFileInfo;
    }

    public function getUploadedFileInfoByField(): array
    {
        $fileInfoByField = [];

        foreach ($this->components as $fieldName => $component) {
            $componentFileInfo = $component->getUploadedFileInfo();
            if (!empty($componentFileInfo)) {
                $fileInfoByField[$fieldName] = $componentFileInfo;
            }
        }

        return $fileInfoByField;
    }

    public function getUploadStats(): array
    {
        $totalStats = [
            'total_files_processed' => 0,
            'total_files_successful' => 0,
            'total_has_errors' => false,
            'total_error_count' => 0,
            'components' => [],
        ];

        foreach ($this->components as $fieldName => $component) {
            $componentStats = $component->getUploadStats();
            $totalStats['components'][$fieldName] = $componentStats;

            $totalStats['total_files_processed'] += $componentStats['files_processed'] ?? 0;
            $totalStats['total_files_successful'] += $componentStats['files_successful'] ?? 0;
            $totalStats['total_error_count'] += $componentStats['error_count'] ?? 0;

            if ($componentStats['has_errors'] ?? false) {
                $totalStats['total_has_errors'] = true;
            }
        }

        return $totalStats;
    }

    public function getFieldMediaPaths(string $fieldName): ?array
    {
        return $this->components[$fieldName]->getUploadedPaths() ?? null;
    }

    public function getFieldErrors(string $fieldName): array
    {
        return $this->components[$fieldName]->getErrors() ?? [];
    }

    public function getFileInformationByField(): array
    {
        $fileInfoByField = [];

        foreach ($this->components as $fieldName => $component) {
            $componentFileInfo = $component->getFileInformationObjects();
            if (!empty($componentFileInfo)) {
                $fileInfoByField[$fieldName] = $componentFileInfo;
            }
        }

        return $fileInfoByField;
    }

    public function getFileInformation(string $fieldName): array
    {
        $component = $this->get($fieldName);
        return $component ? $component->getFileInformationObjects() : [];
    }

    public function getFileInformationObjects(): array
    {
        $allFileInfo = [];

        foreach ($this->components as $fieldName => $component) {
            $componentFileInfo = $component->getFileInformationObjects();
            if (!empty($componentFileInfo)) {
                // Add field context to each file info if needed
                foreach ($componentFileInfo as $fileInfo) {
                    $allFileInfo[] = $fileInfo;
                }
            }
        }

        return $allFileInfo;
    }

    public function hasFieldErrors(string $fieldName): bool
    {
        return !empty($this->components[$fieldName]->getErrors());
    }

    public function getFileMetadata(): array
    {
        $allMetadata = [];
        foreach ($this->components as $fieldName => $component) {
            $componentMetadata = $component->getFileMetadata();

            // Group by field name at the top level
            $allMetadata[$fieldName] = $componentMetadata;
        }
        return $allMetadata;
    }

    public function getFileInfo(int $index): ?FileInformation
    {
        $allFiles = $this->getFileInformationObjects();
        return $allFiles[$index] ?? null;
    }

    public function getTotalFileCount(): int
    {
        return count($this->getFileInformationObjects());
    }

    public function getFileCountByField(): array
    {
        $counts = [];

        foreach ($this->components as $fieldName => $component) {
            $counts[$fieldName] = count($component->getFileInformationObjects());
        }

        return $counts;
    }

    public function isTemporary(): bool
    {
        foreach ($this->components as $component) {
            if ($component->isTemporary()) {
                return true;
            }
        }
        return false;
    }

    public function makePermanent(): bool
    {
        $success = true;
        /** @var AbstractFileUploadService $component */
        foreach ($this->components as $fieldName => $component) {
            if ($this->shouldMakeComponentPermanent($component)) {
                $componentSuccess = $component->makePermanent();

                $success = $componentSuccess && $success;
            } else {
            }
        }

        return $success;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function setFormTemporaryWebPaths(array $fieldWebPaths): FileUploadComponentInterface
    {
        $this->formTemporaryWebPaths = $fieldWebPaths;

        foreach ($this->components as $fieldName => $component) {
            $fieldName = rtrim($fieldName, '[]');
            if (isset($fieldWebPaths[$fieldName])) {
                $paths = is_array($fieldWebPaths[$fieldName]) ? $fieldWebPaths[$fieldName] : [$fieldWebPaths[$fieldName]];
                $component->setFormTemporaryWebPaths($paths);
            }
        }
        return $this;
    }

    public function cleanupOldTempFiles(): int
    {
        $result = 0;
        foreach ($this->components as $component) {
            $result += $component->cleanupOldTempFiles();
        }
        $this->nbOfoldFilesCleanedUp = $result;
        return $result;
    }

    public function getFilesByType(UploadFileType $fileType): array
    {
        return array_filter(
            $this->getFileInformationObjects(),
            fn (FileInformation $fileInfo) => $this->detectFileTypeFromInfo($fileInfo) === $fileType,
        );
    }

    public function hasFilesOfType(UploadFileType $UploadFileType): bool
    {
        return !empty($this->getFilesByType($UploadFileType));
    }

    public function getUploadStatsByType(): array
    {
        $stats = [];
        foreach (UploadFileType::cases() as $fileType) {
            $files = $this->getFilesByType($fileType);
            $stats[$fileType->value] = [
                'count' => count($files),
                'total_size' => array_sum(array_map(
                    fn (FileInformation $file) => $file->getSize(),
                    $files,
                )),
                'files' => $files,
            ];
        }
        return $stats;
    }

    /**
     * @return int
     */
    public function getNbOfoldFilesCleanedUp(): int
    {
        return $this->nbOfoldFilesCleanedUp;
    }

    private function shouldMakeComponentPermanent(FileUploadComponentInterface $component): bool
    {
        // Option 1: Check if component has any media paths (current files)
        if (!empty($component->getMediaPaths())) {
            return true;
        }

        // Option 2: Check if component has temporary web paths (from previous submissions)
        if ($component->hasWebpaths()) {
            return true;
        }

        // Option 3: Check if component has any file information
        if (!empty($component->getFileInformationObjects())) {
            return true;
        }

        return false;
    }

    private function detectFileTypeFromInfo(FileInformation $fileInfo): UploadFileType
    {
        return UploadFileType::fromMimeAndExtension(
            $fileInfo->getMimeType(),
            $fileInfo->getExtension(),
        );
    }

    private function createService(string $serviceClass, string $fieldName): FileUploadComponentInterface
    {
        if (!$this->processor || !$this->fileMover || !$this->request) {
            throw new RuntimeException('Composite service not properly initialized');
        }

        return match($serviceClass) {
            ImageUploadService::class => new ImageUploadService(
                $this->processor->getProcessor(UploadFileType::IMAGE),
                $this->fileMover,
                $this->metadataService,
                $this->request,
                $fieldName,
            ),
            DocumentUploadService::class => new DocumentUploadService(
                $this->processor->getProcessor(UploadFileType::DOCUMENT),
                $this->fileMover,
                $this->request,
                $fieldName,
            ),
            VideoUploadService::class => new VideoUploadService(
                $this->processor->getProcessor(UploadFileType::VIDEO),
                $this->fileMover,
                $this->metadataService,
                $this->request,
                $fieldName,
            ),
            AudioUploadService::class => new AudioUploadService(
                $this->processor->getProcessor(UploadFileType::AUDIO),
                $this->fileMover,
                $this->request,
                $fieldName,
            ),
            default => throw new InvalidArgumentException("Unknown service class: $serviceClass")
        };
    }

    private function fieldHasValidationErrors(string $fieldName): bool
    {
        // Direct field match (e.g., 'main_image')
        if (isset($this->fieldsErrors[$fieldName])) {
            return true;
        }

        // Handle field names with brackets (e.g., 'main_image[]')
        $cleanFieldName = rtrim($fieldName, '[]');
        if (isset($this->fieldsErrors[$cleanFieldName])) {
            return true;
        }

        // Check for any error keys that contain this field name
        foreach (array_keys($this->fieldsErrors) as $errorKey) {
            if (str_contains($errorKey, $cleanFieldName) || str_contains($errorKey, $fieldName)) {
                return true;
            }
        }

        return false;
    }

    public static function createFromRequest(
        FileProcessorRegistry $processor,
        FileMoverService $fileMover,
        FileMetadataService $metadataService,
        Request $request,
        array $fieldErrors,
    ): self {
        $composite = new self($processor, $fileMover, $metadataService, $request, $fieldErrors);
        $files = $request->getFiles();

        foreach ($files->getFieldNames() as $fieldName) {
            $fileData = $files->getFile($fieldName);
            $requiredService = self::determineRequiredService($fieldName, $fileData);

            $leafService = $composite->createService($requiredService, $fieldName);
            $composite->add($fieldName, $leafService);
        }

        return $composite;
    }

    private static function determineRequiredService(string $fieldName, mixed $fileData): string
    {
        // Analyze the actual files, not the field name
        if ($fileData !== null) {
            $files = is_array($fileData) ? $fileData : [$fileData];

            foreach ($files as $file) {
                if ($file instanceof FileUpload && $file->getError()->isSuccess()) {
                    $fileType = UploadFileType::fromFileUpload($file);

                    return match($fileType) {
                        UploadFileType::IMAGE => ImageUploadService::class,
                        UploadFileType::VIDEO => VideoUploadService::class,
                        UploadFileType::AUDIO => AudioUploadService::class,
                        UploadFileType::DOCUMENT => DocumentUploadService::class,
                        UploadFileType::SPREADSHEET => DocumentUploadService::class,
                        UploadFileType::PRESENTATION => DocumentUploadService::class,
                        UploadFileType::ARCHIVE => DocumentUploadService::class,
                        UploadFileType::CODE => DocumentUploadService::class,
                        UploadFileType::FONT => DocumentUploadService::class,
                        default => DocumentUploadService::class,
                    };
                }
            }
        }

        // If we can't determine from files, use a smarter fallback
        return self::getFallbackService($fieldName);
    }

    private static function getFallbackService(string $fieldName): string
    {
        $lowerField = strtolower($fieldName);

        // Check for common image field patterns
        if (str_contains($lowerField, 'image') || str_contains($lowerField, 'img') ||
            str_contains($lowerField, 'photo') || str_contains($lowerField, 'picture') ||
            str_contains($lowerField, 'avatar') || str_contains($lowerField, 'logo')) {
            return ImageUploadService::class;
        }

        // Check for video field patterns
        if (str_contains($lowerField, 'video') || str_contains($lowerField, 'movie') ||
            str_contains($lowerField, 'clip')) {
            return VideoUploadService::class;
        }

        // Check for audio field patterns
        if (str_contains($lowerField, 'audio') || str_contains($lowerField, 'sound') ||
            str_contains($lowerField, 'music') || str_contains($lowerField, 'podcast')) {
            return AudioUploadService::class;
        }

        // Default to document service
        return DocumentUploadService::class;
    }
}