<?php

declare(strict_types=1);

class PathResolver
{
    public function __construct(
        private string $storagePath,
        private string $webBaseUrl = '/uploads/',
    ) {
        // Normalize paths
        $this->storagePath = rtrim($storagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $this->webBaseUrl = rtrim($webBaseUrl, '/') . '/';
    }

    public function toAbsolute(string $webPath, string $targetDir, string $webBasePath): string
    {
        $webPath = str_replace('\\', '/', $webPath);
        $webBasePath = rtrim($webBasePath, '/') . '/';  // Ensure trailing slash
        $targetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);

        if (str_starts_with($webPath, $webBasePath)) {
            $relative = substr($webPath, strlen($webBasePath));
            return $targetDir . DIRECTORY_SEPARATOR . ltrim($relative, '/');
        }

        // Legacy paths
        if (str_starts_with($webPath, '/uploads/')) {
            $relative = substr($webPath, strlen('/uploads/'));
            return $this->storagePath . 'uploads' . DIRECTORY_SEPARATOR . ltrim($relative, '/');
        }

        return $webPath;
    }

    public function toWeb(string $absolutePath, string $targetDir, string $webBasePath): string
    {
        $absolutePath = str_replace('\\', '/', $absolutePath);
        $targetDir = rtrim($targetDir, DIRECTORY_SEPARATOR);

        // DON'T rtrim the webBasePath here - keep the trailing slash
        $webBasePath = $webBasePath;  // Keep as is

        if (str_starts_with($absolutePath, $targetDir)) {
            $relative = substr($absolutePath, strlen($targetDir));
            // Remove leading slash from relative path
            $relative = ltrim($relative, '/');

            // Ensure webBasePath ends with a slash for proper concatenation
            $webBasePath = rtrim($webBasePath, '/') . '/';

            return $webBasePath . $relative;
        }

        // Legacy SRC to SCRIPT conversion
        if (defined('SRC') && defined('SCRIPT') && str_starts_with($absolutePath, SRC)) {
            return str_replace(SRC, SCRIPT . DIRECTORY_SEPARATOR, $absolutePath);
        }

        return $absolutePath;
    }

    public function isTempFile(string $path, string $tempDir): bool
    {
        $tempDir = rtrim($tempDir, DIRECTORY_SEPARATOR);
        return str_starts_with($path, $tempDir);
    }

    public function getStoragePath(): string
    {
        return $this->storagePath;
    }

    public function getWebBaseUrl(): string
    {
        return $this->webBaseUrl;
    }
}
