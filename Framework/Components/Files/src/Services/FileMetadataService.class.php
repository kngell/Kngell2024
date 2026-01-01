<?php

declare(strict_types=1);

class FileMetadataService
{
    private const int PRECISION = 2;

    public function __construct(
        private readonly string $storagePath = STORAGE,
        private readonly string $uploadsPath = STORAGE . 'uploads' . DS,
        private readonly string $staticPath = STORAGE . 'static' . DS,
        private readonly string $tempPath = STORAGE . 'uploads' . DS . 'temp' . DS,
    ) {
    }

    public function getMetadata(FileInformation $fileInfo): array
    {
        $isImage = $fileInfo->isImage();

        return [
            // Basic file information
            'filename' => $fileInfo->getFilename(),
            'display_name' => $this->getDisplayName($fileInfo),
            'extension' => strtolower($fileInfo->getExtension()),
            'mime_type' => $fileInfo->getMimeTypeSafe(),
            'size' => $fileInfo->getSize(),
            'formatted_size' => $this->formatBytes($fileInfo->getSize()),

            // Path information
            'web_path' => $fileInfo->getWebPath(),
            'url' => $fileInfo->getUrl(),
            'relative_path' => $fileInfo->getRelativePath(),
            'absolute_path' => $fileInfo->getPathname(),

            // File type and categorization
            'file_type' => $fileInfo->getFileTypeDescription(),
            'is_image' => $isImage,
            'is_video' => $fileInfo->isVideo(),
            'is_audio' => $fileInfo->isAudio(),
            'is_document' => $fileInfo->isDocument(),
            'is_archive' => $fileInfo->isArchive(),
            'is_text' => $fileInfo->isText(),

            // Location information
            'is_temporary' => $fileInfo->isTemporary(),
            'is_in_storage' => $fileInfo->isInStorage(),
            'is_in_uploads' => $fileInfo->isInUploads(),
            'is_in_static' => $fileInfo->isInStatic(),

            // Security and validation
            'is_safe' => $fileInfo->isSafeForUpload(),
            'has_valid_signature' => $fileInfo->hasValidSignature(),

            // Additional metadata
            'hash' => $fileInfo->isFile() ? $fileInfo->getHash('md5') : null,
            'sha256_hash' => $fileInfo->isFile() ? $fileInfo->getHash('sha256') : null,
            'created_at' => $fileInfo->getCTime(),
            'modified_at' => $fileInfo->getMTime(),
            'formatted_created_at' => $this->formatTimestamp($fileInfo->getCTime()),
            'formatted_modified_at' => $this->formatTimestamp($fileInfo->getMTime()),

            // Image-specific metadata (if applicable)
            'image_info' => $isImage ? $this->getImageInfo($fileInfo) : null,

            // File permissions
            'is_readable' => $fileInfo->isReadable(),
            'is_writable' => $fileInfo->isWritable(),
            'is_executable' => $fileInfo->isExecutable(),
        ];
    }

    public function getUploadMetadata(FileInformation $fileInfo, string $fieldName): array
    {
        $basicMetadata = $this->getMetadata($fileInfo);

        return [
            'original_name' => $fileInfo->getFilename(),
            'display_name' => $this->getDisplayName($fileInfo),
            'size' => $basicMetadata['size'],
            'mime_type' => $basicMetadata['mime_type'],
            'web_path' => $basicMetadata['web_path'],
            'intended_field' => rtrim($fieldName, '[]'),
            'metadata' => $basicMetadata,
        ];
    }

    public function getBatchMetadata(array $fileInfos, ?string $fieldName = null): array
    {
        $metadata = [];

        foreach ($fileInfos as $fileInfo) {
            if (!$fileInfo instanceof FileInformation) {
                continue;
            }

            if ($fieldName) {
                $metadata[] = $this->getUploadMetadata($fileInfo, $fieldName);
            } else {
                $metadata[] = $this->getMetadata($fileInfo);
            }
        }

        return $metadata;
    }

    public function createFromWebPath(string $webPath): ?array
    {
        $absolutePath = $this->webPathToAbsolutePath($webPath);

        if (!file_exists($absolutePath)) {
            return null;
        }

        return $this->getMetadata(new FileInformation($absolutePath));
    }

    public function createFromWebPaths(array $webPathsByField): array
    {
        $results = [];

        foreach ($webPathsByField as $fieldName => $paths) {
            $cleanFieldName = rtrim($fieldName, '[]');

            if (is_array($paths)) {
                foreach ($paths as $path) {
                    if (!empty($path)) {
                        $meta = $this->createFromWebPath($path);
                        if ($meta) {
                            $meta['intended_field'] = $cleanFieldName;
                            $results[$cleanFieldName][] = $meta;
                        }
                    }
                }
            } elseif (!empty($paths)) {
                $meta = $this->createFromWebPath($paths);
                if ($meta) {
                    $meta['intended_field'] = $cleanFieldName;
                    $results[$cleanFieldName][] = $meta;
                }
            }
        }

        return $results;
    }

    public function webPathToAbsolutePath(string $webPath): string
    {
        $webPath = str_replace('\\', '/', $webPath);

        if (str_starts_with($webPath, '/uploads/')) {
            return $this->uploadsPath . ltrim(substr($webPath, 9), '/');
        }

        if (str_starts_with($webPath, '/static/')) {
            return $this->staticPath . ltrim(substr($webPath, 8), '/');
        }

        $filename = basename($webPath);
        $tempFilePath = $this->tempPath . $filename;
        if (file_exists($tempFilePath)) {
            return $tempFilePath;
        }

        if (file_exists($webPath)) {
            return $webPath;
        }

        return $webPath;
    }

    public function absolutePathToWebPath(string $absolutePath): string
    {
        if (str_starts_with($absolutePath, $this->uploadsPath)) {
            return '/uploads/' . ltrim(str_replace($this->uploadsPath, '', $absolutePath), DS);
        }

        if (str_starts_with($absolutePath, $this->staticPath)) {
            return '/static/' . ltrim(str_replace($this->staticPath, '', $absolutePath), DS);
        }

        return $absolutePath;
    }

    public function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $value = $bytes / pow(1024, $pow);

        return round($value, self::PRECISION) . ' ' . $units[$pow];
    }

    private function formatTimestamp(?int $timestamp): ?string
    {
        if (!$timestamp) {
            return null;
        }

        try {
            $date = new DateTime('@' . $timestamp);
            return $date->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }

    private function getDisplayName(FileInformation $fileInfo): string
    {
        $filename = pathinfo($fileInfo->getFilename(), PATHINFO_FILENAME);
        $displayName = str_replace(['_', '-'], ' ', $filename);
        return ucwords($displayName);
    }

    private function getImageInfo(FileInformation $fileInfo): ?array
    {
        if (!$fileInfo->isImage() || !$fileInfo->isReadable()) {
            return null;
        }

        try {
            $imageInfo = getimagesize($fileInfo->getPathname());
            if (!$imageInfo) {
                return null;
            }

            return [
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
        } catch (Throwable $e) {
            return null;
        }
    }

    private function convertToUrl(string $webPath): string
    {
        $path = str_starts_with($webPath, '/') ? $webPath : '/' . $webPath;
        return str_replace(DS, '/', $path);
    }

    /**
     * Check if path is temporary.
     */
    private function isTempPath(string $path): bool
    {
        return str_starts_with($path, $this->tempPath);
    }
}