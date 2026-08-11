<?php

declare(strict_types=1);

abstract class AbstractFileUploadService extends AbstractBaseUpload implements FileUploadComponentInterface, UploadMediapathsInterface
{
    use FileTrimTrait;

    protected FileUploadMap $files;
    protected string $fieldName;
    protected array $keptExistingPaths = [];
    protected array $fileInformation = [];

    public function __construct(
        protected FileProcessorFactory $processor,
        protected FileMoverInterface $fileMover,
        protected FileMetadataService $metadataService,
        protected PathResolver $pathResolver,
        TempFileCleaner $tempFileCleaner,
        Request $request,
        ?string $fieldName = null,
    ) {
        parent::__construct($tempFileCleaner);
        $this->files = $request->getFiles();
        $this->fieldName = $fieldName ?? $this->resolveFieldName();
    }

    public function proceed(bool $uploadRequired = false, bool $temporary = false): void
    {
        $this->errors = [];
        $this->mediaPaths = [];
        $this->fileInformation = [];
        $this->isTemporary = $temporary;

        if (!$this->files->hasFile($this->fieldName)) {
            if ($uploadRequired) {
                $this->addError($this->fieldName, ErrorFile::UPLOAD_ERR_NO_FILE->getErrorMessage());
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

    public function makePermanent(): bool
    {
        $allTempPaths = array_merge($this->mediaPaths, $this->keptExistingPaths);

        if (empty($allTempPaths)) {
            $this->mediaPaths = $this->permanentPaths;
            return true;
        }

        try {
            $tempAbsolutePaths = [];

            foreach ($allTempPaths as $webPath) {
                $absolutePath = $this->webPathToAbsolutePath($webPath);

                if ($this->isTempPath($webPath) && file_exists($absolutePath)) {
                    $tempAbsolutePaths[] = $absolutePath;
                    $this->removeProtectionForFile($absolutePath);
                }
            }

            if (empty($tempAbsolutePaths)) {
                $this->mediaPaths = array_merge($this->permanentPaths, $this->keptExistingPaths);
                $this->keptExistingPaths = [];
                return true;
            }

            $permanentAbsolutePaths = $this->fileMover->makeFilesPermanent(
                $tempAbsolutePaths,
                $this->getTargetDirectory(),
            );

            $newPermanentPaths = array_map(
                fn ($path) => $this->absolutePathToWebPath($path),
                $permanentAbsolutePaths,
            );

            $this->mediaPaths = array_merge($newPermanentPaths, $this->permanentPaths);
            $this->keptExistingPaths = [];
            $this->formTemporaryWebPaths = [];
            $this->isTemporary = false;

            $this->cleanupProcessedFiles($tempAbsolutePaths);

            return true;
        } catch (Throwable $e) {
            $this->addError($this->fieldName, 'Failed to move files to permanent storage: ' . $e->getMessage());
            return false;
        }
    }

    public function setFormTemporaryWebPaths(array $paths, bool $protect = true): self
    {
        $flatPaths = [];
        foreach ($paths as $path) {
            if (is_array($path)) {
                foreach ($path as $nestedPath) {
                    if (is_string($nestedPath) && !empty($nestedPath)) {
                        $flatPaths[] = $nestedPath;
                    }
                }
            } elseif (is_string($path) && !empty($path)) {
                $flatPaths[] = $path;
            }
        }

        $this->formTemporaryWebPaths = $flatPaths;
        $this->keptExistingPaths = [];
        $this->permanentPaths = [];

        foreach ($flatPaths as $webPath) {
            if ($this->isTempPath($webPath)) {
                $this->keptExistingPaths[] = $webPath;
                $absolutePath = $this->webPathToAbsolutePath($webPath);

                // Only protect if explicitly requested
                if ($protect) {
                    $this->markFileAsProtected($absolutePath);
                }
            } else {
                $this->permanentPaths[] = $webPath;
            }
        }

        return $this;
    }

    public function removeProtectionForPaths(array $webPaths): void
    {
        foreach ($webPaths as $webPath) {
            if ($this->isTempPath($webPath)) {
                $absolutePath = $this->webPathToAbsolutePath($webPath);
                $this->removeProtectionForFile($absolutePath);
            }
        }
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

    public function getFileMetadata(): array
    {
        $fieldName = $this->getBaseFieldName($this->fieldName);
        return $this->metadataService->getBatchMetadata($this->fileInformation, $fieldName);
    }

    public function getFileInformationObjects(): array
    {
        return $this->fileInformation;
    }

    public function hasFiles(): bool
    {
        return !empty($this->mediaPaths) || !empty($this->keptExistingPaths) || !empty($this->fileInformation);
    }

    public function getRemovedPermanentPaths(array $originalPaths): array
    {
        return array_diff($originalPaths, $this->permanentPaths);
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function getNewMediaPaths(): array
    {
        return $this->mediaPaths;
    }

    public function getExistingMediaPaths(): array
    {
        return $this->keptExistingPaths;
    }

    public function getRemovedPaths(): array
    {
        return array_diff($this->formTemporaryWebPaths, $this->keptExistingPaths);
    }

    public function hasRemovedPaths(): bool
    {
        return !empty($this->getRemovedPaths());
    }

    public function removeWebPath(string $webPath): void
    {
        // Remove from keptExistingPaths
        $key = array_search($webPath, $this->keptExistingPaths);
        if ($key !== false) {
            unset($this->keptExistingPaths[$key]);
            $this->keptExistingPaths = array_values($this->keptExistingPaths);
        }

        // Remove from formTemporaryWebPaths
        $key = array_search($webPath, $this->formTemporaryWebPaths);
        if ($key !== false) {
            unset($this->formTemporaryWebPaths[$key]);
            $this->formTemporaryWebPaths = array_values($this->formTemporaryWebPaths);
        }
    }

    public function cleanupPermanentFiles(): void
    {
        foreach ($this->mediaPaths as $path) {
            $absolutePath = $this->webPathToAbsolutePath($path);
            if (file_exists($absolutePath)) {
                @unlink($absolutePath);
            }
        }
        $this->mediaPaths = [];
    }

    public function hasWebpaths(): bool
    {
        return !empty($this->mediaPaths) || !empty($this->formTemporaryWebPaths);
    }

    // ========== END OF INTERFACE METHODS ==========

    // Abstract methods that must be implemented by concrete classes
    abstract public function getHandledUploadFileType(): UploadFileType;

    protected function getTempDirectory(): string
    {
        return $this->getTargetDirectory() . 'temp/';
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

        // Fallback logic
        $webPath = str_replace('\\', '/', $webPath);
        $webBasePath = rtrim($this->getWebBasePath(), '/');
        $targetDir = rtrim($this->getTargetDirectory(), DS);

        if (str_starts_with($webPath, $webBasePath)) {
            $relative = substr($webPath, strlen($webBasePath));
            return $targetDir . DS . ltrim($relative, '/');
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

        $tempDir = rtrim($this->getTempDirectory(), DS);
        return str_starts_with($path, $tempDir);
    }

    protected function isTempPath(string $webPath): bool
    {
        // Ensure we have a string
        if (!is_string($webPath)) {
            return false;
        }

        $tempDir = $this->getTempDirectory();
        $tempDirWebPath = $this->absolutePathToWebPath($tempDir);

        $webPathNormalized = rtrim(str_replace('\\', '/', $webPath), '/');
        $tempDirNormalized = rtrim(str_replace('\\', '/', $tempDirWebPath), '/');

        return str_starts_with($webPathNormalized, $tempDirNormalized);
    }

    abstract protected function getTargetDirectory(): string;

    abstract protected function getWebBasePath(): string;

    // Private helper methods
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
            $this->processSingleFile($file, $uploadRequired);
        }
    }

    private function handleFileError(FileUpload $file, bool $uploadRequired): void
    {
        if ($file->getError() === ErrorFile::UPLOAD_ERR_NO_FILE) {
            if ($uploadRequired) {
                $this->addError($this->fieldName, ErrorFile::UPLOAD_ERR_NO_FILE->getErrorMessage($file->getOriginalName()));
            }
        } else {
            $this->addError($this->fieldName, $file->getError()->getErrorMessage($file->getOriginalName()));
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
            $this->addError($this->fieldName, ErrorFile::MOVE_OPERATION_FAILED->getErrorMessage($file->getOriginalName()));
            return null;
        } catch (Throwable $e) {
            $this->addError($this->fieldName, ErrorFile::CREATE_OPERATION_FAILED->getErrorMessage($file->getOriginalName(), $e->getMessage()));
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

    private function resolveFieldName(): string
    {
        $fieldNames = array_keys($this->files->all());
        if (empty($fieldNames)) {
            throw new RuntimeException('No file upload fields found');
        }
        return $fieldNames[0];
    }

    private function getProcessor(): FileProcessorInterface
    {
        $files = $this->files->all();
        if (isset($files[$this->fieldName])) {
            $fileData = $files[$this->fieldName];
            if (is_array($fileData)) {
                $fileData = $fileData[0] ?? null;
            }
            return $this->processor->getFileProcessor($fileData);
        }
        return $this->processor->getFileProcessor(null);
    }
}