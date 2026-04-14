<?php

declare(strict_types=1);
class UploadService implements FileUploadCompositeInterface
{
    private array $components = [];
    private array $errors = [];
    private int $nbOfoldFilesCleanedUp = 0;
    private array $fieldsErrors = [];

    public function __construct(array $fieldsErrors = [])
    {
        $this->fieldsErrors = $fieldsErrors;
    }

    // ========== COMPOSITE-SPECIFIC METHODS ==========

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

    public function getFilePath(string $fieldName): ?string
    {
        $component = $this->get($fieldName);
        if (!$component) {
            return null;
        }

        $paths = $component->getMediaPaths();
        return !empty($paths) ? $paths[0] : null;
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

    // ========== COMPONENT INTERFACE METHODS (DELEGATED) ==========

    public function proceed(bool $uploadRequired = false, bool $temporary = false): void
    {
        $this->errors = [];

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

    public function getMediaPaths(): array
    {
        $allPaths = [];
        foreach ($this->components as $component) {
            $allPaths = array_merge($allPaths, $component->getMediaPaths());
        }
        return $allPaths;
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
        $this->components = [];
        $this->errors = [];
    }

    public function getUploadedFileInfo(): array
    {
        $allInfo = [];
        foreach ($this->components as $component) {
            $allInfo = array_merge($allInfo, $component->getUploadedFileInfo());
        }
        return $allInfo;
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
        foreach ($this->components as $component) {
            if ($component->hasFiles()) {
                $componentSuccess = $component->makePermanent();
                $success = $componentSuccess && $success;
            }
        }
        return $success;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function setFormTemporaryWebPaths(array $formTemporaryWebPaths): self
    {
        foreach ($this->components as $fieldName => $component) {
            $cleanField = rtrim($fieldName, '[]');
            if (isset($formTemporaryWebPaths[$cleanField])) {
                $paths = is_array($formTemporaryWebPaths[$cleanField])
                    ? $formTemporaryWebPaths[$cleanField]
                    : [$formTemporaryWebPaths[$cleanField]];
                $component->setFormTemporaryWebPaths($paths);
            }
        }

        return $this;
    }

    public function getFormTemporaryWebPaths(): array
    {
        return [];
    }

    public function cleanupOldTempFiles(): int
    {
        $total = 0;
        foreach ($this->components as $component) {
            $total += $component->cleanupOldTempFiles();
        }
        $this->nbOfoldFilesCleanedUp = $total;
        return $total;
    }

    public function getNbOfoldFilesCleanedUp(): int
    {
        return $this->nbOfoldFilesCleanedUp;
    }

    public function cleanupPermanentFiles(): void
    {
        foreach ($this->components as $component) {
            $component->cleanupPermanentFiles();
        }
    }

    public function hasWebpaths(): bool
    {
        foreach ($this->components as $component) {
            if ($component->hasWebpaths()) {
                return true;
            }
        }
        return false;
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

    // ========== PRIVATE METHODS ==========

    private function fieldHasValidationErrors(string $fieldName): bool
    {
        $cleanFieldName = rtrim($fieldName, '[]');

        return isset($this->fieldsErrors[$fieldName]) ||
               isset($this->fieldsErrors[$cleanFieldName]);
    }

    public static function createEmpty(array $fieldsErrors = []): self
    {
        return new self($fieldsErrors);
    }
}