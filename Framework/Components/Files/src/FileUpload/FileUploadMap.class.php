<?php

declare(strict_types=1);
class FileUploadMap
{
    use FileTrimTrait;
    private const array FILE_KEYS = ['error', 'name', 'size', 'tmp_name', 'type'];

    /**
     * @var array<string,FileUpload|FileUpload[]>
     */
    private array $files;

    /** @var array<string, FileUpload> Flat, raw indexed map for error tracking */
    private array $indexedFiles = [];

    public function __construct(array $files)
    {
        if (!empty($files)) {
            error_log('Files received: ' . print_r($_FILES, true));
        }
        if (!is_array($files)) {
            throw new InvalidArgumentException('Files must be an array');
        }
        $this->sanitizeFiles($files);
    }

    /**
     * Get the value of files.
     *
     * @return  array<string,FileUpload|FileUpload[]>
     */
    public function all()
    {
        return $this->files;
    }

    public function hasFile(string $name): bool
    {
        $file = $this->getFile($name);

        if ($file instanceof FileUpload) {
            return $file->getError() === ErrorFile::UPLOAD_ERR_OK;
        }

        if (is_array($file)) {
            foreach ($file as $singleFile) {
                if ($singleFile->getError() === ErrorFile::UPLOAD_ERR_OK) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @param int|null $index
     *
     * @return FileUpload[]|FileUpload|null
     */
    public function getFile(string $name, ?int $index = null): array|FileUpload|null
    {
        // Try exact match first
        if (array_key_exists($name, $this->files)) {
            return $this->files[$name];
        }

        // Try multiple file input
        $multipleName = rtrim($name, '[]') . '[]';
        if (array_key_exists($multipleName, $this->files)) {
            return $this->files[$multipleName];
        }

        return null;
    }

    public function getFieldNames(): array
    {
        return array_keys($this->files);
    }

    public function isMultiple(string $name): bool
    {
        $file = $this->getFile($name);
        return is_array($file);
    }

    /**
     * Get first file or null if none.
     */
    public function getFirstFile(string $name): ?FileUpload
    {
        $file = $this->getFile($name);

        if (is_array($file) && !empty($file)) {
            return $file[0];
        }

        return $file instanceof FileUpload ? $file : null;
    }

    public function getName(): string|null
    {
        if (!empty($this->files)) {
            return key($this->files);
        }
        return null;
    }

    public function getAllFiles(): array
    {
        $allFiles = [];
        foreach ($this->files as $fileOrArray) {
            if (is_array($fileOrArray)) {
                $allFiles = array_merge($allFiles, $fileOrArray);
            } else {
                $allFiles[] = $fileOrArray;
            }
        }
        return $allFiles;
    }

    public function hasUploadedFiles(): bool
    {
        foreach ($this->getAllFiles() as $file) {
            if ($file instanceof FileUpload && $file->getError() === UPLOAD_ERR_OK) {
                return true;
            }
        }
        return false;
    }

    public function getFilesByError(int $errorCode): array
    {
        return array_filter(
            $this->getAllFiles(),
            fn ($file) =>
            $file instanceof FileUpload && $file->getError() === $errorCode,
        );
    }

    public function hasOversizedFiles(int $maxSize): bool
    {
        foreach ($this->getAllFiles() as $file) {
            if ($file instanceof FileUpload && $file->getSize() > $maxSize) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get files by MIME type.
     */
    public function getFilesByType(string $mimeType): array
    {
        return array_filter(
            $this->getAllFiles(),
            fn ($file) =>
            $file instanceof FileUpload && $file->getType() === $mimeType,
        );
    }

    /**
     * Get successful uploads only.
     */
    public function getSuccessfulUploads(): array
    {
        return $this->getFilesByError(UPLOAD_ERR_OK);
    }

    /**
     * Count total files (including multiple uploads).
     */
    public function count(): int
    {
        return count($this->getAllFiles());
    }

    /**
     * Check if specific file was uploaded successfully.
     */
    public function isUploaded(string $name, ?int $index = null): bool
    {
        $file = $this->getFile($name, $index);

        if ($file instanceof FileUpload) {
            return $file->getError() === UPLOAD_ERR_OK;
        }

        if (is_array($file)) {
            foreach ($file as $singleFile) {
                if ($singleFile->getError() === UPLOAD_ERR_OK) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if there are any files at all (including errors, empty uploads, etc.).
     */
    public function hasAnyFiles(): bool
    {
        return !empty($this->files);
    }

    /**
     * Check if a specific field has uploaded files (not just empty).
     */
    public function hasUploadedFile(string $fieldName): bool
    {
        $file = $this->getFile($fieldName);

        if ($file instanceof FileUpload) {
            return $file->getError()->isSuccess();
        }

        if (is_array($file)) {
            foreach ($file as $singleFile) {
                if ($singleFile->getError()->isSuccess()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if all file uploads are empty/no file selected.
     */
    public function isEmpty(): bool
    {
        foreach ($this->getAllFiles() as $file) {
            if ($file instanceof FileUpload && $file->getError() !== ErrorFile::UPLOAD_ERR_NO_FILE) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get count of successfully uploaded files.
     */
    public function countUploadedFiles(): int
    {
        $count = 0;
        foreach ($this->getAllFiles() as $file) {
            if ($file instanceof FileUpload && $file->getError()->isSuccess()) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Get count of all file inputs (including empty ones).
     */
    public function countAllFileInputs(): int
    {
        return count($this->files);
    }

    private function sanitizeFiles(array $files): void
    {
        $this->files = [];

        $normalizedFiles = FileRequestNormalizer::normalize($files);

        foreach ($normalizedFiles as $rawFieldName => $fileData) {
            if (!$this->isValidFileStructure($fileData)) {
                continue;
            }

            $fileUploadInstance = $this->sanitizeSingleFile($fileData);

            $this->indexedFiles[$rawFieldName] = $fileUploadInstance;

            $targetComponentName = $this->getBaseFieldName($rawFieldName);

            // 3. Group files under the exact target array component name
            if (str_ends_with($rawFieldName, ']') || str_contains($rawFieldName, '[')) {
                $this->files[$targetComponentName][] = $fileUploadInstance;
            } else {
                $this->files[$targetComponentName] = $fileUploadInstance;
            }
        }
    }

    private function isValidFileStructure(array $file): bool
    {
        // Just check if all required keys exist
        foreach (self::FILE_KEYS as $requiredKey) {
            if (!array_key_exists($requiredKey, $file)) {
                return false;
            }
        }

        return true;
    }

    private function sanitizeSingleFile(array $file): FileUpload
    {
        $error = (int) $file['error'];
        return new FileUpload(
            $file['tmp_name'] ?? '',
            $file['name'] ?? '',
            $file['type'] ?? '',
            ErrorFile::from($error),
            (int) ($file['size'] ?? 0),
        );
    }

    private function sanitizeFileArray(array $arrayFile): array
    {
        $files = [];
        $fileCount = count($arrayFile['name'] ?? []);

        for ($i = 0; $i < $fileCount; $i++) {
            $fileData = [];
            foreach (self::FILE_KEYS as $key) {
                $fileData[$key] = $arrayFile[$key][$i] ?? null;
            }

            if ($this->isValidFileStructure($fileData)) {
                $files[] = $this->sanitizeSingleFile($fileData);
            }
        }

        return $files;
    }
}