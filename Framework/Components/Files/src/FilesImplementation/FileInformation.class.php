<?php

declare(strict_types=1);

class FileInformation extends SplFileInfo
{
    private const string UPLOADS = STORAGE . 'uploads' . DS;
    private const string STATIC = STORAGE . 'static' . DS;

    public function __construct(string $path)
    {
        parent::__construct($path);
    }

    public function getMimeType(): string
    {
        // First, check if the file is actually accessible for reading
        if (!$this->isFileAccessible()) {
            return $this->getMimeTypeFromExtension();
        }

        // Try fileinfo detection with proper error handling
        try {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detected = $finfo->file($this->getPathname());

            // Validate that we got a meaningful result
            if ($detected && $detected !== 'application/octet-stream' && $detected !== 'inode/x-empty') {
                return $detected;
            }
        } catch (Throwable $e) {
            // Log the error but continue with fallback
            error_log("Fileinfo detection failed for {$this->getPathname()}: " . $e->getMessage());
        }

        // Fallback to extension-based detection using your comprehensive constants
        return $this->getMimeTypeFromExtension();
    }

    /**
     * Enhanced robust MIME type detection with multiple fallback strategies.
     */
    public function getRobustMimeType(): string
    {
        // Strategy 1: Direct file detection (most accurate when file is accessible)
        if ($this->isFileAccessible()) {
            try {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $detected = $finfo->file($this->getPathname());

                // Validate the detection result
                if ($this->isValidMimeType($detected)) {
                    return $detected;
                }
            } catch (Throwable $e) {
                // Continue to next strategy
            }
        }

        // Strategy 2: Use the comprehensive extension mapping from MimeTypeConstants
        $extension = strtolower($this->getExtension());
        if (!empty($extension)) {
            $mimeType = $this->getMimeTypeFromExtension();
            if ($mimeType !== 'application/octet-stream') {
                return $mimeType;
            }
        }

        // Strategy 3: For FileUpload objects, the parent getMimeType() already handles this
        // FileUpload overrides getMimeType() with its own logic using the mimeType property
        // So we don't need a separate strategy here

        // Strategy 4: Final fallback
        return 'application/octet-stream';
    }

    /**
     * Safe version that never throws exceptions - perfect for metadata extraction.
     */
    public function getMimeTypeSafe(): string
    {
        try {
            return $this->getRobustMimeType();
        } catch (Throwable $e) {
            // Ultimate fallback - use extension mapping only
            return $this->getMimeTypeFromExtension();
        }
    }

    /**
     * Get all possible MIME types for this file based on extension.
     */
    public function getPossibleMimeTypes(): array
    {
        $extension = $this->getExtension();
        return MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension] ?? [];
    }

    /**
     * Get file extension from MIME type.
     */
    public function getExtensionFromMimeType(): ?string
    {
        $mimeType = $this->getMimeType();
        $extensions = MimeTypeConstants::MIME_TYPE_TO_EXTENSION[$mimeType] ?? [];

        return !empty($extensions) ? $extensions[0] : null;
    }

    /**
     * Get all possible extensions for this file's MIME type.
     */
    public function getPossibleExtensions(): array
    {
        $mimeType = $this->getMimeType();
        return MimeTypeConstants::MIME_TYPE_TO_EXTENSION[$mimeType] ?? [];
    }

    /**
     * Check if file is of specific MIME type.
     */
    public function isMimeType(string $mimeType): bool
    {
        return $this->getMimeType() === $mimeType;
    }

    /**
     * Check if file is in a category of MIME types.
     */
    public function isMimeCategory(string $category): bool
    {
        $mimeType = $this->getMimeType();
        return str_starts_with($mimeType, $category . '/');
    }

    /**
     * Enhanced type checking methods.
     */
    public function isImage(): bool
    {
        return $this->isMimeCategory('image');
    }

    public function isVideo(): bool
    {
        return $this->isMimeCategory('video');
    }

    public function isAudio(): bool
    {
        return $this->isMimeCategory('audio');
    }

    public function isText(): bool
    {
        return $this->isMimeCategory('text');
    }

    public function isApplication(): bool
    {
        return $this->isMimeCategory('application');
    }

    public function isArchive(): bool
    {
        $archiveMimes = [
            'application/zip', 'application/x-zip-compressed',
            'application/x-rar-compressed', 'application/x-7z-compressed',
            'application/gzip', 'application/x-tar',
        ];

        return in_array($this->getMimeType(), $archiveMimes);
    }

    public function isDocument(): bool
    {
        $documentMimes = [
            'application/pdf', 'application/msword', 'application/vnd.ms-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.oasis.opendocument.text',
        ];

        return in_array($this->getMimeType(), $documentMimes);
    }

    /**
     * Security validation methods.
     */
    public function isSafeForUpload(): bool
    {
        $dangerousMimes = [
            'application/x-msdownload', // Windows executables
            'application/x-dosexec',    // DOS/Windows executables
            'application/x-sh',         // Shell scripts
            'application/x-php',        // PHP files
            'application/x-httpd-php',  // PHP files
        ];

        return !in_array($this->getMimeType(), $dangerousMimes);
    }

    public function isAllowedForUpload(array $allowedMimeTypes): bool
    {
        return in_array($this->getMimeType(), $allowedMimeTypes);
    }

    /**
     * Get human-readable file type description.
     */
    public function getFileTypeDescription(): string
    {
        $mimeType = $this->getMimeType();

        return match (true) {
            $this->isImage() => 'Image File',
            $this->isVideo() => 'Video File',
            $this->isAudio() => 'Audio File',
            $this->isText() => 'Text File',
            $this->isArchive() => 'Archive File',
            $this->isDocument() => 'Document File',
            str_starts_with($mimeType, 'application/') => 'Application File',
            default => 'File'
        };
    }

    /**
     * Validate file signature (magic numbers) for extra security.
     */
    public function hasValidSignature(): bool
    {
        if (!$this->isFile() || !$this->isReadable()) {
            return false;
        }

        $expectedMime = $this->getMimeType();
        $detectedMime = $this->detectMimeFromSignature();

        return $expectedMime === $detectedMime;
    }

    /**
     * Get web path - converts storage paths to web-accessible URLs.
     */
    public function getWebPath(): string
    {
        $absolutePath = $this->getPathname();

        // Convert storage path to web URL (uploads)
        if (str_starts_with($absolutePath, self::UPLOADS)) {
            $relativePath = str_replace(self::UPLOADS, '', $absolutePath);
            return '/uploads/' . $relativePath;
        }

        // Convert storage path to web URL (static files)
        if (str_starts_with($absolutePath, self::STATIC)) {
            $relativePath = str_replace(self::STATIC, '', $absolutePath);
            return '/static/' . $relativePath;
        }

        // Legacy support: Convert SRC path to web path (SCRIPT constant)
        if (defined('SRC') && str_starts_with($absolutePath, SRC)) {
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
     * Get the relative path from the document root.
     */
    public function getRelativePath(): string
    {
        $webPath = $this->getWebPath();

        // Remove leading slash for relative path
        if (str_starts_with($webPath, '/')) {
            return ltrim($webPath, '/');
        }

        return $webPath;
    }

    /**
     * Get file URL for web access.
     */
    public function getUrl(): string
    {
        $webPath = $this->getWebPath();

        // Ensure the path starts with a slash
        if (!str_starts_with($webPath, '/')) {
            $webPath = '/' . $webPath;
        }

        // Convert directory separators to URL slashes
        return str_replace(DS, '/', $webPath);
    }

    /**
     * Check if file is in storage directory.
     */
    public function isInStorage(): bool
    {
        $absolutePath = $this->getPathname();

        return str_starts_with($absolutePath, STORAGE);
    }

    /**
     * Check if file is in uploads directory.
     */
    public function isInUploads(): bool
    {
        $absolutePath = $this->getPathname();

        return str_starts_with($absolutePath, self::UPLOADS);
    }

    /**
     * Check if file is in static directory.
     */
    public function isInStatic(): bool
    {
        $absolutePath = $this->getPathname();

        return str_starts_with($absolutePath, self::STATIC);
    }

    /**
     * Check if file is temporary (in temp directory).
     */
    public function isTemporary(): bool
    {
        $absolutePath = $this->getPathname();
        $tempDir = self::UPLOADS . 'temp' . DS;

        return str_starts_with($absolutePath, $tempDir);
    }

    /**
     * Get storage relative path (path within storage directory).
     */
    public function getStorageRelativePath(): string
    {
        $absolutePath = $this->getPathname();

        if ($this->isInStorage()) {
            return str_replace(STORAGE, '', $absolutePath);
        }

        return $absolutePath;
    }

    /**
     * Get file size in human-readable format.
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->getSize();
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get file creation/modification dates.
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        $timestamp = $this->getCTime();
        return $timestamp ? new DateTimeImmutable('@' . $timestamp) : null;
    }

    public function getModifiedAt(): ?DateTimeImmutable
    {
        $timestamp = $this->getMTime();
        return $timestamp ? new DateTimeImmutable('@' . $timestamp) : null;
    }

    /**
     * Get file hash for integrity checking.
     */
    public function getHash(string $algorithm = 'md5'): string
    {
        return hash_file($algorithm, $this->getPathname());
    }

    /**
     * Check if file is within a specific directory.
     */
    public function isWithinDirectory(string $directory): bool
    {
        $filePath = $this->getRealPath();
        $directory = realpath($directory);

        if ($filePath === false || $directory === false) {
            return false;
        }

        return str_starts_with($filePath, $directory);
    }

    /**
     * Get provided MIME type (for FileUpload objects).
     */
    protected function getProvidedMimeType(): string
    {
        // Check if this is a FileUpload instance and has the mimeType property
        if ($this instanceof FileUpload) {
            // Try to access the mimeType property via reflection if it's private
            try {
                $reflection = new ReflectionClass($this);
                $property = $reflection->getProperty('mimeType');
                $property->setAccessible(true);
                return $property->getValue($this) ?? '';
            } catch (ReflectionException $e) {
                // If reflection fails, try direct property access
                if (isset($this->mimeType)) {
                    return $this->mimeType;
                }
            }
        }

        return '';
    }

    /**
     * Check if a MIME type is valid and meaningful.
     */
    protected function isValidMimeType(?string $mimeType): bool
    {
        if (empty($mimeType)) {
            return false;
        }

        $invalidTypes = [
            'application/octet-stream',
            'inode/x-empty',
            'inode/x-empty; charset=binary',
            'text/plain',
            'text/plain; charset=us-ascii',
        ];

        return !in_array($mimeType, $invalidTypes);
    }

    /**
     * Get MIME type from file extension using the comprehensive MimeTypeConstants.
     */
    private function getMimeTypeFromExtension(): string
    {
        $extension = strtolower($this->getExtension());

        if (!empty($extension) && isset(MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension])) {
            $mimeTypes = MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension];
            return $mimeTypes[0] ?? 'application/octet-stream';
        }

        return 'application/octet-stream';
    }

    /**
     * Check if file is accessible for reading operations.
     */
    private function isFileAccessible(): bool
    {
        $path = $this->getPathname();

        // Check if path is empty, invalid, or points to a non-existent file
        if (empty($path) || $path === '' || !file_exists($path)) {
            return false;
        }

        // Check if it's actually a file (not a directory) and readable
        if (!$this->isFile() || !$this->isReadable()) {
            return false;
        }

        // Check file size - empty files can cause issues with fileinfo
        $size = $this->getSize();
        if ($size === 0 || $size === false) {
            return false;
        }

        return true;
    }

    private function detectMimeFromSignature(): string
    {
        $handle = fopen($this->getPathname(), 'rb');
        if (!$handle) {
            return 'application/octet-stream';
        }

        $header = fread($handle, 1024);
        fclose($handle);

        // Check common magic numbers
        if (str_starts_with($header, "\xFF\xD8\xFF")) {
            return 'image/jpeg';
        }
        if (str_starts_with($header, "\x89PNG\r\n\x1A\n")) {
            return 'image/png';
        }
        if (str_starts_with($header, 'GIF8')) {
            return 'image/gif';
        }
        if (str_starts_with($header, '%PDF')) {
            return 'application/pdf';
        }
        if (str_starts_with($header, "PK\x03\x04")) {
            return 'application/zip';
        }
        if (str_starts_with($header, "Rar!\x1A\x07")) {
            return 'application/x-rar-compressed';
        }

        return 'application/octet-stream';
    }
}