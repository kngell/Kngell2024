<?php

declare(strict_types=1);

class UploadService extends AbstractBaseUpload implements FileUploadCompositeInterface, UploadMediapathsInterface
{
    use FileTrimTrait;

    private array $components = [];
    private array $fieldsErrors = [];

    public function __construct(TempFileCleaner $tempFileCleaner, array $fieldsErrors = [])
    {
        parent::__construct($tempFileCleaner);
        $this->fieldsErrors = $fieldsErrors;
    }

    // ========== COMPOSITE METHODS ==========

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

    // ========== FILE UPLOAD METHODS ==========

    public function proceed(bool $uploadRequired = false, bool $temporary = false): void
    {
        $this->errors = [];
        $this->mediaPaths = [];

        foreach ($this->components as $fieldName => $component) {
            if ($this->fieldHasValidationErrors($fieldName)) {
                continue;
            }

            $component->proceed($uploadRequired, $temporary);
            $componentErrors = $component->getErrors();

            if (!empty($componentErrors)) {
                $this->errors[$fieldName] = $componentErrors;
            }
        }
    }

    public function makePermanent(): bool
    {
        $success = true;

        foreach ($this->components as $component) {
            if ($component->hasFiles()) {
                $componentSuccess = $component->makePermanent();
                $success = $componentSuccess && $success;
            }
        }

        return $success;
    }

    // ========== GETTER METHODS ==========

    public function getFilePath(string $fieldName): null|string|array
    {
        $component = $this->get($fieldName);
        if (!$component) {
            return null;
        }

        $paths = $component->getMediaPaths();
        return !empty($paths) ? $paths : null;
    }

    public function getMultiFilePaths(string $fieldName): array
    {
        $component = $this->get($fieldName);
        return $component ? $component->getMediaPaths() : [];
    }

    public function getFileInformationByField(): array
    {
        $result = [];
        foreach ($this->components as $fieldName => $component) {
            $info = $component->getFileInformationObjects();
            if (!empty($info)) {
                $result[$fieldName] = $info;
            }
        }
        return $result;
    }

    public function getMediaPathsByField(): array
    {
        $result = [];
        foreach ($this->components as $fieldName => $component) {
            $paths = $component->getMediaPaths();
            if (!empty($paths)) {
                $cleanField = $this->getBaseFieldName($fieldName); //rtrim($fieldName, '[]');
                $result[$cleanField] = $paths;
            }
        }
        return $result;
    }

    public function getFileMetadataByField(): array
    {
        $result = [];
        foreach ($this->components as $fieldName => $component) {
            $metadata = $component->getFileMetadata();
            if (!empty($metadata)) {
                $result[$fieldName] = $metadata;
            }
        }
        return $result;
    }

    public function hasFieldErrors(string $fieldName): bool
    {
        $component = $this->get($fieldName);
        return $component ? $component->hasErrors() : false;
    }

    public function getFieldErrors(string $fieldName): array
    {
        $component = $this->get($fieldName);
        return $component ? $component->getErrors() : [];
    }

    public function getFieldMediaPaths(string $fieldName): ?array
    {
        $component = $this->get($fieldName);
        $paths = $component ? $component->getMediaPaths() : [];
        return !empty($paths) ? $paths : null;
    }

    public function getMetadata(): array
    {
        $allMetadata = [];
        foreach ($this->components as $fieldName => $component) {
            $metadata = $component->getFileMetadata();
            if (!empty($metadata)) {
                foreach ($metadata as $fileMetadata) {
                    $allMetadata[$fieldName][] = $fileMetadata['metadata'] ?? $fileMetadata;
                }
            }
        }
        return $allMetadata;
    }

    public function getTotalFileCount(): int
    {
        $count = 0;
        foreach ($this->components as $component) {
            $count += count($component->getFileInformationObjects());
        }
        return $count;
    }

    public function getFileCountByField(): array
    {
        $result = [];
        foreach ($this->components as $fieldName => $component) {
            $result[$fieldName] = count($component->getFileInformationObjects());
        }
        return $result;
    }

    public function getUploadedFileInfo(): array
    {
        $allInfo = [];
        foreach ($this->components as $component) {
            $allInfo = array_merge($allInfo, $component->getUploadedFileInfo());
        }
        return $allInfo;
    }

    public function getFileInformationObjects(): array
    {
        $allInfo = [];
        foreach ($this->components as $component) {
            $allInfo = array_merge($allInfo, $component->getFileInformationObjects());
        }
        return $allInfo;
    }

    public function getFileMetadata(): array
    {
        $allMetadata = [];
        foreach ($this->components as $fieldName => $component) {
            $metadata = $component->getFileMetadata();
            if (!empty($metadata)) {
                $allMetadata[$fieldName] = $metadata;
            }
        }
        return $allMetadata;
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

    public function setFormTemporaryWebPaths(array $formTemporaryWebPaths): self
    {
        foreach ($this->components as $fieldName => $component) {
            $cleanField = $this->getBaseFieldName($fieldName);

            if (isset($formTemporaryWebPaths[$cleanField])) {
                $paths = is_array($formTemporaryWebPaths[$cleanField])
                    ? $formTemporaryWebPaths[$cleanField]
                    : [$formTemporaryWebPaths[$cleanField]];

                $component->setFormTemporaryWebPaths($paths);
            }
        }

        return $this;
    }

    public function cleanupOrphanedFiles(array $activePaths = []): int
    {
        $totalCleaned = 0;

        foreach ($this->components as $component) {
            if ($component instanceof FileCleanupInterface) {
                $totalCleaned += $component->cleanupOrphanedFiles($activePaths);
            }
        }

        return $totalCleaned;
    }

    public function getAllFieldsName(): array
    {
        return array_keys($this->components) ?? [];
    }

    public function getFieldName(): string
    {
        $fieldNames = array_keys($this->components);
        return $fieldNames[0] ?? '';
    }

    public function hasFiles(): bool
    {
        foreach ($this->components as $component) {
            if ($component->hasFiles()) {
                return true;
            }
        }
        return false;
    }

    // ========== ABSTRACT METHODS IMPLEMENTATION ==========

    protected function getTempDirectory(): string
    {
        foreach ($this->components as $component) {
            if (method_exists($component, 'getTempDirectory')) {
                return $component->getTempDirectory();
            }
        }
        return STORAGE . 'uploads/images/temp';
    }

    protected function webPathToAbsolutePath(string $webPath): string
    {
        foreach ($this->components as $component) {
            if (method_exists($component, 'webPathToAbsolutePath')) {
                return $component->webPathToAbsolutePath($webPath);
            }
        }
        return $webPath;
    }

    protected function absolutePathToWebPath(string $absolutePath): string
    {
        foreach ($this->components as $component) {
            if (method_exists($component, 'absolutePathToWebPath')) {
                return $component->absolutePathToWebPath($absolutePath);
            }
        }
        return $absolutePath;
    }

    // ========== PRIVATE METHODS ==========

    private function fieldHasValidationErrors(string $fieldName): bool
    {
        $cleanFieldName = rtrim($fieldName, '[]');
        return isset($this->fieldsErrors[$fieldName]) ||
               isset($this->fieldsErrors[$cleanFieldName]);
    }
}