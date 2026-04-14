<?php

declare(strict_types=1);

abstract class AbstractFileUploadService implements FileUploadComponentInterface, UploadMediapathsInterface
{
    private const int TEMP_FILE_MAX_AGE = 3600;

    protected FileUploadMap $files;
    protected array $errors = [];
    protected array $mediaPaths = [];
    protected array $fileInformation = [];
    protected string $fieldName;
    protected bool $isTemporary = false;
    protected array $formTemporaryWebPaths = [];
    private array $keptExistingPaths = [];
    private int $nbOfoldFilesCleanedUp = 0;

    public function __construct(
        protected FileProcessorFactory $processor,
        protected FileMoverInterface $fileMover,
        protected FileMetadataService $metadataService,
        protected PathResolver $pathResolver,
        protected TempFileCleaner $tempFileCleaner,
        Request $request,
        ?string $fieldName = null,
    ) {
        $this->files = $request->getFiles();
        $this->fieldName = $fieldName ?? $this->resolveFieldName();
        // $this->state = FileUploadState::createInitial();
    }

    // ========== REQUIRED INTERFACE METHODS ==========

    public function proceed(bool $uploadRequired = false, bool $temporary = false): void
    {
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
    }

    public function getMediaPaths(): array
    {
        return array_merge($this->keptExistingPaths, $this->mediaPaths);
    }

    public function getExistingMediaPaths(): array
    {
        return $this->keptExistingPaths;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function cleanupOldTempFiles(): int
    {
        // If we have access to a cleaner, use it
        if (isset($this->tempFileCleaner)) {
            return $this->tempFileCleaner->cleanupDirectory($this->getTempDirectory());
        }

        $cleanedCount = 0;
        $now = time();
        $tempDir = $this->getTempDirectory();

        foreach (glob($tempDir . '*') as $file) {
            if (is_file($file) && ($now - filemtime($file)) > self::TEMP_FILE_MAX_AGE) {
                if (unlink($file)) {
                    $cleanedCount++;
                }
            }
        }

        $this->nbOfoldFilesCleanedUp = $cleanedCount;
        return $cleanedCount;
    }

    public function cleanup(): void
    {
        foreach ($this->mediaPaths as $webPath) {
            $absolutePath = $this->webPathToAbsolutePath($webPath);
            if (file_exists($absolutePath)) {
                @unlink($absolutePath);
            }
        }
        $this->mediaPaths = [];
        $this->fileInformation = [];
        $this->formTemporaryWebPaths = [];
    }

    public function getUploadedFileInfo(): array
    {
        return $this->metadataService->getBatchMetadata($this->fileInformation);
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
            'files_successful' => count($this->mediaPaths),
            'has_errors' => $this->hasErrors(),
            'error_count' => array_sum(array_map('count', $this->errors)),
        ];
    }

    public function getFileInformationObjects(): array
    {
        return $this->fileInformation;
    }

    public function getFileMetadata(): array
    {
        $fieldName = rtrim($this->fieldName, '[]');
        return $this->metadataService->getBatchMetadata($this->fileInformation, $fieldName);
    }

    public function isTemporary(): bool
    {
        return $this->isTemporary;
    }

    public function getRemovedPaths(): array
    {
        return array_diff($this->formTemporaryWebPaths, $this->keptExistingPaths);
    }

    public function hasRemovedPaths(): bool
    {
        return !empty($this->getRemovedPaths());
    }

    public function makePermanent(): bool
    {
        if (empty($this->mediaPaths) && empty($this->keptExistingPaths)) {
            return true;
        }

        try {
            // Use keptExistingPaths, not formTemporaryWebPaths!
            $allWebPaths = array_merge($this->mediaPaths, $this->keptExistingPaths);
            $tempPaths = [];

            foreach ($allWebPaths as $webPath) {
                $absolutePath = $this->webPathToAbsolutePath($webPath);
                if ($this->isTempFile($absolutePath) && file_exists($absolutePath)) {
                    $tempPaths[] = $absolutePath;
                }
            }

            if (empty($tempPaths)) {
                return true;
            }

            $permanentPaths = $this->fileMover->makeFilesPermanent(
                $tempPaths,
                $this->getTargetDirectory(),
            );

            $this->mediaPaths = array_map(
                fn ($path) => $this->absolutePathToWebPath($path),
                $permanentPaths,
            );
            $this->isTemporary = false;
            return true;
        } catch (Throwable $e) {
            $this->addError(ErrorFile::MOVE_OPERATION_FAILED, 'system', $e->getMessage());
            return false;
        }
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function setFormTemporaryWebPaths(array $formTemporaryWebPaths): self
    {
        $this->formTemporaryWebPaths = $formTemporaryWebPaths;
        $this->keptExistingPaths = $formTemporaryWebPaths;
        // $this->state = FileUploadState::createInitial($formTemporaryWebPaths);
        return $this;
    }

    public function removeWebPath(string $webPath): void
    {
        // Remove from keptExistingPaths
        $key = array_search($webPath, $this->keptExistingPaths);
        if ($key !== false) {
            unset($this->keptExistingPaths[$key]);
            $this->keptExistingPaths = array_values($this->keptExistingPaths);
        }

        $key = array_search($webPath, $this->formTemporaryWebPaths);
        if ($key !== false) {
            unset($this->formTemporaryWebPaths[$key]);
            $this->formTemporaryWebPaths = array_values($this->formTemporaryWebPaths);
        }
    }

    public function getNewMediaPaths(): array
    {
        return $this->mediaPaths;
    }

    public function getFormTemporaryWebPaths(): array
    {
        return $this->formTemporaryWebPaths;
    }

    public function getNbOfoldFilesCleanedUp(): int
    {
        return $this->nbOfoldFilesCleanedUp;
    }

    public function cleanupPermanentFiles(): void
    {
        foreach ($this->mediaPaths as $path) {
            $this->fileMover->deletePermanentFile($path);
        }
        $this->mediaPaths = [];
    }

    public function hasWebpaths(): bool
    {
        return !empty($this->mediaPaths) || !empty($this->formTemporaryWebPaths);
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function hasFiles(): bool
    {
        return !empty($this->getMediaPaths()) || !empty($this->fileInformation);
    }

    // ========== ABSTRACT METHODS ==========

    abstract public function getHandledUploadFileType(): UploadFileType;

    public function getWebBasePath(): string
    {
        $storagePath = realpath(STORAGE) . DIRECTORY_SEPARATOR;
        // Ensure target directory has a consistent slash for calculation
        $targetDir = rtrim($this->getTargetDirectory(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        if (str_starts_with($targetDir, $storagePath)) {
            $relative = substr($targetDir, strlen($storagePath));
            // Ensure the return string starts and ends with a slash
            return '/' . trim(str_replace(DIRECTORY_SEPARATOR, '/', $relative), '/') . '/';
        }

        return '/uploads/' . basename($targetDir) . '/';
    }

    // public function getWebBasePath(): string
    // {
    //     $storagePath = realpath(STORAGE) . DIRECTORY_SEPARATOR;
    //     $targetDir = $this->getTargetDirectory();

    //     if (str_starts_with($targetDir, $storagePath)) {
    //         $relative = substr($targetDir, strlen($storagePath));
    //         return '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    //     }

    //     return '/uploads/' . basename($targetDir) . '/';
    // }

    abstract protected function getTargetDirectory(): string;

    protected function getProcessor(): FileProcessorInterface
    {
        $files = $this->files->all();
        if (is_array($files[$this->fieldName])) {
            return $this->processor->getFileProcessor($files[$this->fieldName][0]);
        }
        return $this->processor->getFileProcessor($files[$this->fieldName]);
    }

    // ========== PROTECTED METHODS ==========

    protected function getTempDirectory(): string
    {
        return $this->getTargetDirectory() . 'temp/';
    }

    protected function resolveFieldName(): string
    {
        $fieldNames = array_keys($this->files->all());
        if (empty($fieldNames)) {
            throw new RuntimeException('No file upload fields found');
        }
        return $fieldNames[0];
    }

    protected function webPathToAbsolutePath(string $webPath): string
    {
        if ($this->pathResolver !== null) {
            return $this->pathResolver->toAbsolute(
                $webPath,
                $this->getTargetDirectory(),
                $this->getWebBasePath(),
            );
        }

        // Fallback to simple logic
        $webPath = str_replace('\\', '/', $webPath);
        $webBasePath = rtrim($this->getWebBasePath(), '/');
        $targetDir = rtrim($this->getTargetDirectory(), DIRECTORY_SEPARATOR);

        if (str_starts_with($webPath, $webBasePath)) {
            $relative = substr($webPath, strlen($webBasePath));
            return $targetDir . DIRECTORY_SEPARATOR . ltrim($relative, '/');
        }

        return file_exists($webPath) ? $webPath : $webPath;
    }

    protected function absolutePathToWebPath(string $absolutePath): string
    {
        if ($this->pathResolver !== null) {
            return $this->pathResolver->toWeb(
                $absolutePath,
                $this->getTargetDirectory(),
                $this->getWebBasePath(),
            );
        }

        $absolutePath = str_replace('\\', '/', $absolutePath);
        $targetDir = rtrim(str_replace('\\', '/', $this->getTargetDirectory()), '/');
        $webBasePath = rtrim($this->getWebBasePath(), '/');

        if (str_starts_with($absolutePath, $targetDir)) {
            $relative = substr($absolutePath, strlen($targetDir));

            return $webBasePath . '/' . ltrim($relative, '/');
        }

        return $absolutePath;
    }

    protected function isTempFile(string $path): bool
    {
        if ($this->pathResolver !== null) {
            return $this->pathResolver->isTempFile($path, $this->getTempDirectory());
        }

        $tempDir = rtrim($this->getTempDirectory(), DIRECTORY_SEPARATOR);
        return str_starts_with($path, $tempDir);
    }

    // ========== PRIVATE METHODS ==========

    private function processSingleFile(FileUpload $file, bool $uploadRequired): void
    {
        if ($file->getError() !== ErrorFile::UPLOAD_ERR_OK) {
            $this->handleFileError($file, $uploadRequired);
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

            if ($file->getError() !== ErrorFile::UPLOAD_ERR_OK) {
                $this->handleFileError($file, $uploadRequired);
                continue;
            }

            $fileInfo = $this->saveFile($file);
            if ($fileInfo !== null) {
                $this->mediaPaths[] = $fileInfo->getWebPath();
                $this->fileInformation[] = $fileInfo;
            }
        }
    }

    private function handleFileError(FileUpload $file, bool $uploadRequired): void
    {
        if ($file->getError() === ErrorFile::UPLOAD_ERR_NO_FILE) {
            if ($uploadRequired) {
                $this->addError(ErrorFile::UPLOAD_ERR_NO_FILE, $file->getOriginalName());
            }
        } else {
            $this->addError($file->getError(), $file->getOriginalName());
        }
    }

    private function saveFile(FileUpload $file): ?FileInformation
    {
        try {
            $safeFilename = $this->generateSafeFilename($file);
            $targetDir = $this->isTemporary ? $this->getTempDirectory() : $this->getTargetDirectory();

            return $this->fileMover->moveUploadedFile(
                upload: $file,
                targetDirectory: $targetDir,
                filename: $safeFilename,
                processor: $this->getProcessor(),
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

    private function addError(ErrorFile $error, ?string $filename = null, string ...$params): void
    {
        $field = $this->fieldName;
        $message = $error->getErrorMessage($filename, ...$params);

        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }
}
