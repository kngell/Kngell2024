<?php

declare(strict_types=1);

class FileInformation extends SplFileInfo
{
    private const string UPLOADS = STORAGE . 'uploads' . DS;
    private const string STATIC = STORAGE . 'static' . DS;
    private const string TEMP = STORAGE . 'uploads' . DS . 'temp' . DS;

    private ?string $cachedMimeType = null;
    private ?array $cachedImageInfo = null;
    private ?string $cachedWebPath = null;
    private ?string $cachedHash = null;
    private ?bool $cachedIsImage = null;

    public function __construct(string $path)
    {
        $resolvedPath = $this->resolvePath($path);
        parent::__construct($resolvedPath);
    }

    private function resolvePath(string $path): string
    {
        if (empty($path)) {
            return $path;
        }

        if (file_exists($path)) {
            return $path;
        }

        // Handle web-to-absolute resolution if path starts with /
        if (str_starts_with($path, '/')) {
            // Check assets (SRC)
            if (defined('SRC')) {
                $script = defined('SCRIPT') ? SCRIPT : '/';
                $script = rtrim($script, '/\\');
                $assetsPrefix = $script . '/assets/';
                
                if (str_starts_with($path, $assetsPrefix)) {
                    $relative = substr($path, strlen($script));
                    $fullPath = SRC . ltrim($relative, '/\\');
                    if (file_exists($fullPath)) {
                        return $fullPath;
                    }
                }
            }

            // Check if it's in public/assets (since document root might be public)
            if (defined('ROOT_DIR')) {
                $publicPath = rtrim(ROOT_DIR, '/\\') . DS . 'public' . DS . ltrim($path, '/\\');
                if (file_exists($publicPath)) {
                    return $publicPath;
                }
            }

            // Check storage (uploads/static)
            if (defined('STORAGE')) {
                if (str_starts_with($path, '/uploads/')) {
                    $fullPath = STORAGE . 'uploads' . DS . ltrim(substr($path, 9), '/\\');
                    if (file_exists($fullPath)) {
                        return $fullPath;
                    }
                }
                if (str_starts_with($path, '/static/')) {
                    $fullPath = STORAGE . 'static' . DS . ltrim(substr($path, 8), '/\\');
                    if (file_exists($fullPath)) {
                        return $fullPath;
                    }
                }
            }
        }

        return $path;
    }

    public function getMimeType(): string
    {
        if ($this->cachedMimeType !== null) {
            return $this->cachedMimeType;
        }
        if ($this->isFileAccessible()) {
            try {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $detected = $finfo->file($this->getPathname());

                if ($detected && $detected !== 'application/octet-stream' && $detected !== 'inode/x-empty') {
                    $this->cachedMimeType = $detected;
                    return $this->cachedMimeType;
                }
            } catch (Throwable $e) {
                // Continue to fallback
            }
        }
        $this->cachedMimeType = $this->getMimeTypeFromExtension();
        return $this->cachedMimeType;
    }

    public function getMimeTypeSafe(): string
    {
        try {
            return $this->getMimeType();
        } catch (Throwable $e) {
            return $this->getMimeTypeFromExtension();
        }
    }

    public function getPossibleMimeTypes(): array
    {
        $extension = strtolower($this->getExtension());
        return MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension] ?? [];
    }

    public function getExtensionFromMimeType(): ?string
    {
        $mimeType = $this->getMimeType();
        $extensions = MimeTypeConstants::MIME_TYPE_TO_EXTENSION[$mimeType] ?? [];

        return !empty($extensions) ? $extensions[0] : null;
    }

    public function isMimeType(string $mimeType): bool
    {
        return $this->getMimeType() === $mimeType;
    }

    public function isMimeCategory(string $category): bool
    {
        $mimeType = $this->getMimeType();
        return str_starts_with($mimeType, $category . '/');
    }

    public function isImage(): bool
    {
        if ($this->cachedIsImage !== null) {
            return $this->cachedIsImage;
        }

        $this->cachedIsImage = $this->isMimeCategory('image');
        return $this->cachedIsImage;
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

        return in_array($this->getMimeType(), $archiveMimes, true);
    }

    public function isDocument(): bool
    {
        $documentMimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.ms-word',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.oasis.opendocument.text',
        ];

        return in_array($this->getMimeType(), $documentMimes, true);
    }

    public function isSafeForUpload(): bool
    {
        $dangerousMimes = [
            'application/x-msdownload', // Windows executables
            'application/x-dosexec',    // DOS/Windows executables
            'application/x-sh',         // Shell scripts
            'application/x-php',        // PHP files
            'application/x-httpd-php',  // PHP files
        ];

        return !in_array($this->getMimeType(), $dangerousMimes, true);
    }

    public function isAllowedForUpload(array $allowedMimeTypes): bool
    {
        return in_array($this->getMimeType(), $allowedMimeTypes, true);
    }

    public function getFileTypeDescription(): string
    {
        return match (true) {
            $this->isImage() => 'Image File',
            $this->isVideo() => 'Video File',
            $this->isAudio() => 'Audio File',
            $this->isText() => 'Text File',
            $this->isArchive() => 'Archive File',
            $this->isDocument() => 'Document File',
            $this->isApplication() => 'Application File',
            default => 'File'
        };
    }

    public function hasValidSignature(): bool
    {
        if (!$this->isFile() || !$this->isReadable()) {
            return false;
        }

        $expectedMime = $this->getMimeType();
        $detectedMime = $this->detectMimeFromSignature();

        return $expectedMime === $detectedMime;
    }

    public function getImageInfo(): ?array
    {
        if (!$this->isImage() || !$this->isReadable()) {
            return null;
        }

        if ($this->cachedImageInfo !== null) {
            return $this->cachedImageInfo;
        }

        try {
            $imageInfo = getimagesize($this->getPathname());
            if (!$imageInfo) {
                return null;
            }

            $this->cachedImageInfo = [
                'width' => $imageInfo[0] ?? null,
                'height' => $imageInfo[1] ?? null,
                'type' => $imageInfo[2] ?? null,
                'bits' => $imageInfo['bits'] ?? null,
                'channels' => $imageInfo['channels'] ?? null,
                'mime' => $imageInfo['mime'] ?? null,
                'aspect_ratio' => $imageInfo[0] && $imageInfo[1]
                    ? round($imageInfo[0] / $imageInfo[1], 2)
                    : null,
            ];

            return $this->cachedImageInfo;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function getWebPath(): string
    {
        if ($this->cachedWebPath !== null) {
            return $this->cachedWebPath;
        }

        $absolutePath = $this->getPathname();

        if (str_starts_with($absolutePath, self::UPLOADS)) {
            $this->cachedWebPath = '/uploads/' . ltrim(str_replace(self::UPLOADS, '', $absolutePath), DS);
        } elseif (str_starts_with($absolutePath, self::STATIC)) {
            $this->cachedWebPath = '/static/' . ltrim(str_replace(self::STATIC, '', $absolutePath), DS);
        } elseif (defined('SRC') && str_starts_with($absolutePath, SRC)) {
            $this->cachedWebPath = str_replace(SRC, SCRIPT . DS, $absolutePath);
        } elseif (defined('SCRIPT') && str_starts_with($absolutePath, SCRIPT . DS)) {
            $this->cachedWebPath = $absolutePath;
        } else {
            $this->cachedWebPath = $absolutePath;
        }

        return $this->cachedWebPath;
    }

    public function getRelativePath(): string
    {
        $webPath = $this->getWebPath();
        return str_starts_with($webPath, '/') ? ltrim($webPath, '/') : $webPath;
    }

    public function getUrl(): string
    {
        $webPath = $this->getWebPath();

        if (!str_starts_with($webPath, '/')) {
            $webPath = '/' . $webPath;
        }

        return str_replace(DS, '/', $webPath);
    }

    public function isInStorage(): bool
    {
        return str_starts_with($this->getPathname(), STORAGE);
    }

    public function isInUploads(): bool
    {
        return str_starts_with($this->getPathname(), self::UPLOADS);
    }

    public function isInStatic(): bool
    {
        return str_starts_with($this->getPathname(), self::STATIC);
    }

    public function isTemporary(): bool
    {
        return str_starts_with($this->getPathname(), self::TEMP);
    }

    public function getStorageRelativePath(): string
    {
        $absolutePath = $this->getPathname();

        if ($this->isInStorage()) {
            return ltrim(str_replace(STORAGE, '', $absolutePath), DS);
        }

        return $absolutePath;
    }

    public function getFormattedSize(int $precision = 2): string
    {
        $bytes = $this->getSize();
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

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

    public function getHash(string $algorithm = 'md5'): string
    {
        if ($algorithm === 'md5' && $this->cachedHash !== null) {
            return $this->cachedHash;
        }

        $hash = hash_file($algorithm, $this->getPathname());

        if ($algorithm === 'md5') {
            $this->cachedHash = $hash;
        }

        return $hash;
    }

    public function getHashes(array $algorithms = ['md5', 'sha1', 'sha256']): array
    {
        $hashes = [];

        foreach ($algorithms as $algorithm) {
            $hashes[$algorithm] = $this->getHash($algorithm);
        }

        return $hashes;
    }

    public function isWithinDirectory(string $directory): bool
    {
        $filePath = $this->getRealPath();
        $directory = realpath($directory);

        if ($filePath === false || $directory === false) {
            return false;
        }

        return str_starts_with($filePath, $directory);
    }

    public function getContents(): ?string
    {
        if (!$this->isFile() || !$this->isReadable()) {
            return null;
        }

        try {
            return file_get_contents($this->getPathname());
        } catch (Throwable $e) {
            return null;
        }
    }

    public function getLines(): array
    {
        if (!$this->isFile() || !$this->isReadable()) {
            return [];
        }

        try {
            $lines = file($this->getPathname(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            return $lines ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    public function clearCache(): void
    {
        $this->cachedMimeType = null;
        $this->cachedImageInfo = null;
        $this->cachedWebPath = null;
        $this->cachedHash = null;
        $this->cachedIsImage = null;
    }

    public function exists(): bool
    {
        return file_exists($this->getPathname());
    }

    public function getPermissions(): string
    {
        $perms = $this->getPerms();
        return substr(sprintf('%o', $perms), -4);
    }

    public function getOwnerId(): int
    {
        return $this->getOwner();
    }

    public function getGroupId(): int
    {
        return $this->getGroup();
    }

    private function getMimeTypeFromExtension(): string
    {
        $extension = strtolower($this->getExtension());

        if (!empty($extension) && isset(MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension])) {
            $mimeTypes = MimeTypeConstants::EXTENSION_TO_MIME_TYPES[$extension];
            return $mimeTypes[0] ?? 'application/octet-stream';
        }

        return 'application/octet-stream';
    }

    private function isFileAccessible(): bool
    {
        $path = $this->getPathname();

        // Check if path is empty or invalid
        if (empty($path) || $path === '') {
            return false;
        }

        // Check if file exists
        if (!file_exists($path)) {
            return false;
        }

        // Check if it's actually a file and readable
        if (!$this->isFile() || !$this->isReadable()) {
            return false;
        }

        // Check file size - empty files can cause issues with fileinfo
        $size = $this->getSize();
        return $size !== false && $size > 0;
    }

    private function detectMimeFromSignature(): string
    {
        if (!$this->isFile() || !$this->isReadable()) {
            return 'application/octet-stream';
        }

        try {
            $handle = fopen($this->getPathname(), 'rb');
            if (!$handle) {
                return 'application/octet-stream';
            }

            $header = fread($handle, 12); // Read only first 12 bytes (enough for most signatures)
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
            if (str_starts_with($header, "\x1A\x45\xDF\xA3")) {
                return 'video/webm';
            }
        } catch (Throwable $e) {
            // Silent fallback
        }

        return 'application/octet-stream';
    }
}