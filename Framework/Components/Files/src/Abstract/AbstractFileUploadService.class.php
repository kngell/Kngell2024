<?php

declare(strict_types=1);

abstract class AbstractFileUploadService implements FileUploadComponentInterface
{
    private const int TEMP_FILE_MAX_AGE = 3600;

    protected FileUploadMap $files;
    protected array $errors = [];
    protected ?array $mediaPaths = null;
    protected ?array $fileInformation = null;
    protected ?string $fieldName;
    protected bool $isTemporary = false;

    /**
     * @var array<string>
     */
    protected array $formTemporaryWebPaths = [];

    private int $nbOfoldFilesCleanedUp = 0;

    public function __construct(
        protected FileMoverService $fileMover,
        protected FileMetadataService $metadataService,
        Request $request,
        ?string $fieldName = null,
    ) {
        $this->files = $request->getFiles();
        $this->fieldName = $fieldName ?? $this->resolveFieldName();
        $this->fileInformation = [];
        $this->mediaPaths = [];
    }

    /**
     * Get the file type this service handles.
     */
    abstract public function getHandledUploadFileType(): UploadFileType;

    /**
     * Get the base storage directory for this file type.
     * This is used for organization within storage.
     */
    abstract public function getStorageBaseDirectory(): string;

    /**
     * Get the web-accessible base path for this file type.
     * This maps to the URL path for serving files.
     */
    abstract public function getWebBasePath(): string;

    /**
     * Get all directories managed by this service.
     * Useful for permissions setup and cleanup.
     */
    public function getManagedDirectories(): array
    {
        return [
            'target' => $this->getTargetDirectory(),
            'temp' => $this->getTempDirectory(),
        ];
    }

    /**
     * Check if directories are properly accessible.
     */
    public function areDirectoriesReady(): bool
    {
        $targetDir = $this->getTargetDirectory();
        $tempDir = $this->getTempDirectory();

        // FileMover will create directories when needed, so we just check if they're writable
        return is_writable(dirname($targetDir)) && is_writable(dirname($tempDir));
    }

    /**
     * Get directory status information.
     */
    public function getDirectoryStatus(): array
    {
        $targetDir = $this->getTargetDirectory();
        $tempDir = $this->getTempDirectory();

        return [
            'target_directory' => [
                'path' => $targetDir,
                'exists' => is_dir($targetDir),
                'writable' => is_writable($targetDir),
            ],
            'temp_directory' => [
                'path' => $tempDir,
                'exists' => is_dir($tempDir),
                'writable' => is_writable($tempDir),
            ],
            'parent_writable' => [
                'target_parent' => is_writable(dirname($targetDir)),
                'temp_parent' => is_writable(dirname($tempDir)),
            ],
            'service_type' => $this->getHandledUploadFileType()->value,
        ];
    }

    public function proceed(bool $uploadRequired = false, bool $temporary = false): void
    {
        try {
            $this->errors = [];
            $this->mediaPaths = [];
            $this->fileInformation = [];
            $this->isTemporary = $temporary;

            if (!$this->files->hasFile($this->fieldName)) {
                if ($uploadRequired) {
                    $this->addError(ErrorFile::UPLOAD_ERR_NO_FILE);
                }
                return;
            }

            $fileOrFiles = $this->files->getFile($this->fieldName);

            if ($fileOrFiles === null) {
                return;
            }

            if (is_array($fileOrFiles)) {
                $this->processMultipleFiles($fileOrFiles, $uploadRequired);
            } else {
                $this->processSingleFile($fileOrFiles, $uploadRequired);
            }
        } catch (Throwable $th) {
            $this->addError(ErrorFile::CREATE_OPERATION_FAILED, 'system', $th->getMessage());
            throw $th;
        }
    }

    public function getMediaPaths(): ?array
    {
        return $this->mediaPaths;
    }

    public function hasWebpaths(): bool
    {
        return isset($this->formTemporaryWebPaths) && !empty($this->formTemporaryWebPaths);
    }

    public function cleanupPermanentFiles(): void
    {
        if (!empty($this->mediaPaths)) {
            foreach ($this->mediaPaths as $path) {
                $this->fileMover->deletePermanentFile($path);
            }

            $this->mediaPaths = [];
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFileInformationObjects(): array
    {
        return $this->fileInformation ?? [];
    }

    public function getFileInfo(int $index): ?FileInformation
    {
        return $this->fileInformation[$index] ?? null;
    }

    public function getUploadedPaths(): ?array
    {
        return $this->mediaPaths;
    }

    public function isTemporary(): bool
    {
        return $this->isTemporary;
    }

    public function deleteAbsoluteTempFiles(array $absolutePaths): void
    {
        foreach ($absolutePaths as $tempPath) {
            if (str_starts_with($tempPath, $this->getTempDirectory()) && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    public function cleanupTempFiles(array $tempPaths): void
    {
        foreach ($tempPaths as $tempPath) {
            if (file_exists($tempPath) && str_starts_with($tempPath, $this->getTempDirectory())) {
                unlink($tempPath);
            }
        }
    }

    public function cleanupOldTempFiles(): int
    {
        $cleanedCount = 0;
        $now = time();

        foreach (glob($this->getTempDirectory() . '*') as $file) {
            if (is_file($file) && ($now - filemtime($file)) > self::TEMP_FILE_MAX_AGE) {
                if (unlink($file)) {
                    $cleanedCount++;
                }
            }
        }

        return $cleanedCount;
    }

    public function getUploadedFileInfo(): array
    {
        if (empty($this->fileInformation)) {
            return [];
        }
        return $this->metadataService->getBatchMetadata($this->fileInformation);
    }

    public function isTempFile(string $path): bool
    {
        $r = $this->getTempDirectory();
        return str_starts_with($path, $this->getTempDirectory());
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getFirstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return null;
    }

    public function getUserFriendlyErrors(): array
    {
        $friendlyErrors = [];

        foreach ($this->errors as $field => $errors) {
            $friendlyErrors[$field] = [];
            foreach ($errors as $error) {
                if (str_contains($error, 'partially uploaded')) {
                    $friendlyErrors[$field][] = 'File was only partially uploaded';
                } elseif (str_contains($error, 'No file was uploaded')) {
                    $friendlyErrors[$field][] = 'Please select a file';
                } elseif (str_contains($error, 'temporary directory')) {
                    $friendlyErrors[$field][] = 'System error: Unable to save files';
                } elseif (str_contains($error, 'write file') || str_contains($error, 'move file')) {
                    $friendlyErrors[$field][] = 'Unable to save file';
                } else {
                    $friendlyErrors[$field][] = $error;
                }
            }
        }

        return $friendlyErrors;
    }

    public function hasFieldErrors(string $fieldName): bool
    {
        return !empty($this->errors[$fieldName]);
    }

    public function getFieldErrors(string $fieldName): array
    {
        return $this->errors[$fieldName] ?? [];
    }

    public function cleanup(): void
    {
        if ($this->mediaPaths) {
            foreach ($this->mediaPaths as $webPath) {
                $absolutePath = $this->webPathToAbsolutePath($webPath);
                if (file_exists($absolutePath)) {
                    unlink($absolutePath);
                }
            }
            $this->mediaPaths = [];
            $this->fileInformation = [];
        }
    }

    public function getUploadStats(): array
    {
        $fileOrFiles = $this->files->getFile($this->fieldName);
        $fileCount = 0;

        if ($fileOrFiles !== null) {
            $fileCount = is_array($fileOrFiles) ? count($fileOrFiles) : 1;
        }

        return [
            'field_name' => $this->fieldName,
            'files_processed' => $fileCount,
            'files_successful' => count($this->mediaPaths ?? []),
            'has_errors' => $this->hasErrors(),
            'error_count' => array_sum(array_map('count', $this->errors)),
        ];
    }

    public function getFileMetadata(): array
    {
        $fieldName = rtrim($this->fieldName, '[]');

        return $this->metadataService->getBatchMetadata(
            $this->fileInformation ?? [],
            $fieldName,
        );
    }

    public function makePermanent(): bool
    {
        $allWebPathsToPromote = $this->mediaPaths ?? [];
        $allWebPathsToPromote = array_merge($allWebPathsToPromote, $this->formTemporaryWebPaths);
        $allWebPathsToPromote = array_unique($allWebPathsToPromote);

        if (empty($allWebPathsToPromote)) {
            return true;
        }

        try {
            $absoluteTempPaths = [];
            $absolutePathsToCleanup = [];

            foreach ($allWebPathsToPromote as $webPath) {
                $absolutePath = $this->webPathToAbsolutePath($webPath);

                if ($this->isTempFile($absolutePath) && file_exists($absolutePath)) {
                    $absoluteTempPaths[] = $absolutePath;
                }

                $absolutePathsToCleanup[] = $absolutePath;
            }

            if (empty($absoluteTempPaths)) {
                $this->deleteAbsoluteTempFiles($absolutePathsToCleanup);
                return true;
            }

            $permanentPaths = $this->fileMover->makeFilesPermanent(
                $absoluteTempPaths,
                $this->getTargetDirectory(),
            );

            // Convert back to web paths
            $permanentWebPaths = [];
            foreach ($permanentPaths as $absolutePath) {
                $permanentWebPaths[] = $this->absolutePathToWebPath($absolutePath);
            }

            $this->mediaPaths = $permanentWebPaths;
            $this->isTemporary = false;

            // Update file information with new permanent paths
            $this->updateFileInformationPaths($permanentPaths);

            $this->deleteAbsoluteTempFiles($absolutePathsToCleanup);
            $this->formTemporaryWebPaths = [];

            return true;
        } catch (Throwable $e) {
            $this->addError(ErrorFile::MOVE_OPERATION_FAILED, 'system', $e->getMessage());
            return false;
        }
    }

    /**
     * @return array
     */
    public function getFormTemporaryWebPaths(): array
    {
        return $this->formTemporaryWebPaths;
    }

    /**
     * @param array $formTemporaryWebPaths
     *
     * @return FileUploadComponentInterface
     */
    public function setFormTemporaryWebPaths(array $formTemporaryWebPaths): FileUploadComponentInterface
    {
        $this->formTemporaryWebPaths = $formTemporaryWebPaths;
        return $this;
    }

    /**
     * @return int
     */
    public function getNbOfoldFilesCleanedUp(): int
    {
        return $this->nbOfoldFilesCleanedUp;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    protected function resolveFieldName(): string
    {
        $fieldNames = array_keys($this->files->all());

        if (empty($fieldNames)) {
            throw new RuntimeException('No file upload fields found');
        }

        // Return the actual field name with brackets if present
        return $fieldNames[0];
    }

    abstract protected function getProcessor(): object;

    /**
     * Get target directory for permanent files.
     */
    abstract protected function getTargetDirectory(): string;

    /**
     * Get temporary directory.
     */
    abstract protected function getTempDirectory(): string;

    /**
     * Convert web path to absolute filesystem path.
     */
    protected function webPathToAbsolutePath(string $webPath): string
    {
        // Normalize web path (ensure consistent slashes)
        $webPath = str_replace('\\', '/', $webPath);

        // Use the service-specific web base path
        $webBasePath = $this->getWebBasePath();
        $targetDir = $this->getTargetDirectory();

        // Check if web path starts with our service's web base path
        if (str_starts_with($webPath, $webBasePath)) {
            $relativePath = substr($webPath, strlen($webBasePath));
            // Ensure we don't get double slashes
            $relativePath = ltrim($relativePath, '/');
            return $targetDir . $relativePath;
        }

        // Try temp directory conversion - temp files might have different web path
        $tempDir = $this->getTempDirectory();

        // For temp files, they might be served from a different web path
        // Let's check if the file exists in temp directory first
        $filename = basename($webPath);
        $tempFilePath = $tempDir . $filename;

        if (file_exists($tempFilePath)) {
            return $tempFilePath;
        }

        // Check if it's a storage-based path (fallback for migrated files)
        if (str_starts_with($webPath, '/uploads/')) {
            $relativePath = substr($webPath, strlen('/uploads/'));
            $relativePath = ltrim($relativePath, '/');
            return STORAGE . 'uploads' . DS . $relativePath;
        }

        if (str_starts_with($webPath, '/static/')) {
            $relativePath = substr($webPath, strlen('/static/'));
            $relativePath = ltrim($relativePath, '/');
            return STORAGE . 'static' . DS . $relativePath;
        }

        // Legacy support: SRC/SCRIPT conversion
        if (defined('SCRIPT') && defined('SRC') && str_starts_with($webPath, SCRIPT . DS)) {
            return str_replace(SCRIPT . DS, SRC, $webPath);
        }

        // If it's already an absolute filesystem path, return as is
        if (file_exists($webPath)) {
            return $webPath;
        }

        // Final fallback: try to construct path from target directory
        $filename = basename($webPath);
        $fallbackPath = $targetDir . $filename;

        return file_exists($fallbackPath) ? $fallbackPath : $webPath;
    }

    /**
     * Convert absolute filesystem path to web path.
     */
    protected function absolutePathToWebPath(string $absolutePath): string
    {
        // Try service-specific target directory conversion
        $targetDir = $this->getTargetDirectory();
        $webBasePath = $this->getWebBasePath();

        if (str_starts_with($absolutePath, $targetDir)) {
            $relativePath = str_replace($targetDir, '', $absolutePath);
            return $webBasePath . $relativePath;
        }

        // Try temp directory conversion
        $tempDir = $this->getTempDirectory();
        if (str_starts_with($absolutePath, $tempDir)) {
            $relativePath = str_replace($tempDir, '', $absolutePath);
            return $webBasePath . $relativePath;
        }

        // Legacy support: SRC to SCRIPT conversion
        if (defined('SRC') && defined('SCRIPT') && str_starts_with($absolutePath, SRC)) {
            return str_replace(SRC, SCRIPT . DS, $absolutePath);
        }

        // If it's already a web path, return as is
        if (defined('SCRIPT') && str_starts_with($absolutePath, SCRIPT . DS)) {
            return $absolutePath;
        }

        // Fallback: return as relative path
        return $absolutePath;
    }

    /**
     * Update file information objects with new permanent paths.
     */
    private function updateFileInformationPaths(array $permanentPaths): void
    {
        // Clear and rebuild fileInformation from ALL permanent paths
        $this->fileInformation = [];

        foreach ($permanentPaths as $permanentPath) {
            $this->fileInformation[] = new FileInformation($permanentPath);
        }
    }

    private function processSingleFile(FileUpload $file, bool $uploadRequired): void
    {
        // Only process valid files (UPLOAD_ERR_OK)
        if (!$file->getError()->isSuccess()) {
            return;
        }

        if (!$this->checkSystemErrors($file, $uploadRequired)) {
            return;
        }

        $fileInfo = $this->saveFile($file);
        if ($fileInfo !== null) {
            $this->mediaPaths[] = $fileInfo->getWebPath();
            $this->fileInformation[] = $fileInfo;
        }
    }

    private function processMultipleFiles(array $files, bool $uploadRequired): void
    {
        foreach ($files as $file) {
            if (!$file instanceof FileUpload) {
                continue;
            }

            // Only process valid files (UPLOAD_ERR_OK)
            if (!$file->getError()->isSuccess()) {
                continue;
            }

            if ($this->checkSystemErrors($file, $uploadRequired)) {
                $fileInfo = $this->saveFile($file);
                if ($fileInfo !== null) {
                    $this->mediaPaths[] = $fileInfo->getWebPath();
                    $this->fileInformation[] = $fileInfo;
                }
            }
        }
    }

    private function checkSystemErrors(FileUpload $file, bool $uploadRequired): bool
    {
        // Handle "no file" case specifically
        if ($file->getError() === ErrorFile::UPLOAD_ERR_NO_FILE) {
            if ($uploadRequired) {
                $this->addError(ErrorFile::UPLOAD_ERR_NO_FILE, $file->getOriginalName());
                return false;
            }
            // If not required, just skip this file

            return false;
        }

        // Handle actual upload errors
        if (!$file->getError()->isSuccess()) {
            $this->addError($file->getError(), $file->getOriginalName());
            return false;
        }

        return true;
    }

    private function saveFile(FileUpload $file): ?FileInformation
    {
        try {
            $safeFilename = $this->generateSafeFilename($file);
            $targetDirectory = $this->isTemporary ? $this->getTempDirectory() : $this->getTargetDirectory();

            return $this->fileMover->moveUploadedFile(
                upload: $file,
                targetDirectory: $targetDirectory,
                filename: $safeFilename,
                processor: $this->getProcessor(), // Use abstract method
            );
        } catch (FileException $e) {
            $this->addError(ErrorFile::MOVE_OPERATION_FAILED, $file->getOriginalName());
            return null;
        } catch (Throwable $e) {
            $this->addError(ErrorFile::CREATE_OPERATION_FAILED, $file->getOriginalName(), $e->getMessage());
            return null;
        }
    }

    private function generateSafeFilename(FileUpload $file): string
    {
        $extension = strtolower($file->getOriginalExtension());
        $baseName = pathinfo($file->getOriginalName(), PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName);

        return $safeName . '_' . uniqid() . '.' . $extension;
    }

    private function addError(ErrorFile $error, ?string $filename = null, ...$params): void
    {
        $field = $this->fieldName;
        $message = $error->getErrorMessage($filename, ...$params);

        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }
}