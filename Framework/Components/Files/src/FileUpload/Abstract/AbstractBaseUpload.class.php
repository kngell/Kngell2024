<?php

declare(strict_types=1);

abstract class AbstractBaseUpload implements FileCleanupInterface
{
    protected TempFileCleaner $tempFileCleaner;
    protected array $protectedFiles = [];
    protected array $errors = [];
    protected array $mediaPaths = [];
    protected array $formTemporaryWebPaths = [];
    protected array $keptExistingPaths = [];
    protected array $permanentPaths = [];
    protected bool $isTemporary = false;
    protected int $nbOfOldFilesCleanedUp = 0;

    public function __construct(TempFileCleaner $tempFileCleaner)
    {
        $this->tempFileCleaner = $tempFileCleaner;
    }

    public function cleanupProcessedFiles(array $filePaths): int
    {
        return $this->tempFileCleaner->cleanSpecificFiles($filePaths);
    }

    public function cleanupOrphanedFiles(array $activePaths = []): int
    {
        $tempDir = $this->getTempDirectory();
        $allFiles = $this->getAllTempFiles($tempDir);
        $cleaned = 0;

        foreach ($allFiles as $file) {
            $webPath = $this->absolutePathToWebPath($file);

            if (in_array($webPath, $activePaths)) {
                continue;
            }

            if (isset($this->protectedFiles[$file]) || $this->isFileProtected($file)) {
                continue;
            }

            if ($this->tempFileCleaner->cleanSpecificFiles([$file]) > 0) {
                $cleaned++;
            }
        }

        return $cleaned;
    }

    public function cleanupAgedFiles(int $maxAgeSeconds = 3600): int
    {
        return $this->tempFileCleaner->clean($this->getTempDirectory(), $maxAgeSeconds);
    }

    public function cleanupOldTempFiles(): int
    {
        $cleaned = $this->tempFileCleaner->clean($this->getTempDirectory(), 3600);
        $this->nbOfOldFilesCleanedUp = $cleaned;
        return $cleaned;
    }

    public function getNbOfOldFilesCleanedUp(): int
    {
        return $this->nbOfOldFilesCleanedUp;
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

    public function cleanup(): void
    {
        foreach ($this->mediaPaths as $webPath) {
            $absolutePath = $this->webPathToAbsolutePath($webPath);
            if (file_exists($absolutePath)) {
                @unlink($absolutePath);
            }
        }
        foreach ($this->formTemporaryWebPaths as $webPath) {
            $absolutePath = $this->webPathToAbsolutePath($webPath);
            if (file_exists($absolutePath)) {
                @unlink($absolutePath);
            }
        }
        $this->mediaPaths = [];
        $this->formTemporaryWebPaths = [];
        $this->keptExistingPaths = [];
    }

    public function hasWebpaths(): bool
    {
        return !empty($this->mediaPaths) || !empty($this->formTemporaryWebPaths);
    }

    public function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getMediaPaths(): array
    {
        return array_merge($this->mediaPaths, $this->keptExistingPaths, $this->permanentPaths);
    }

    public function getExistingMediaPaths(): array
    {
        return $this->keptExistingPaths;
    }

    public function getPermanentMediaPaths(): array
    {
        return $this->permanentPaths;
    }

    public function getNewMediaPaths(): array
    {
        return $this->mediaPaths;
    }

    public function getRemovedPaths(): array
    {
        return array_diff($this->formTemporaryWebPaths, $this->keptExistingPaths);
    }

    public function getFormTemporaryWebPaths(): array
    {
        return $this->formTemporaryWebPaths;
    }

    public function isTemporary(): bool
    {
        return $this->isTemporary;
    }

    public function getFieldName(): string
    {
        return '';
    }

    public function getAllFieldsName(): array
    {
        return [];
    }

    public function hasFiles(): bool
    {
        return !empty($this->mediaPaths) || !empty($this->keptExistingPaths) || !empty($this->fileInformation);
    }

    protected function markFileAsProtected(string $absolutePath): void
    {
        $this->protectedFiles[$absolutePath] = true;
        $markerFile = $absolutePath . '.protected';
        file_put_contents($markerFile, session_id());
    }

    protected function unmarkFileAsProtected(string $absolutePath): void
    {
        $markerFile = $absolutePath . '.protected';
        if (file_exists($markerFile)) {
            unlink($markerFile);
        }
        unset($this->protectedFiles[$absolutePath]);
    }

    protected function isFileProtected(string $absolutePath): bool
    {
        if (isset($this->protectedFiles[$absolutePath])) {
            return true;
        }

        $markerFile = $absolutePath . '.protected';
        if (file_exists($markerFile)) {
            $sessionId = file_get_contents($markerFile);
            if ($sessionId === session_id()) {
                return true;
            }
            if ((time() - filemtime($markerFile)) < 3600) {
                return true;
            }
            // Clean up expired protection
            unlink($markerFile);
        }

        return false;
    }

    protected function removeProtectionForFile(string $absolutePath): void
    {
        $markerFile = $absolutePath . '.protected';
        if (file_exists($markerFile)) {
            unlink($markerFile);
        }
        unset($this->protectedFiles[$absolutePath]);
    }

    protected function getAllTempFiles(string $tempDir): array
    {
        $files = [];
        if (!is_dir($tempDir)) {
            return $files;
        }

        foreach (glob($tempDir . '*') as $item) {
            if (is_file($item) && !str_ends_with($item, '.protected')) {
                $files[] = $item;
            }
        }
        return $files;
    }

    protected function getTempDirFromPath(string $webPath): ?string
    {
        if (str_contains($webPath, '/temp/')) {
            return substr($webPath, 0, strpos($webPath, '/temp/') + 6);
        }
        if (str_contains($webPath, '/tmp/')) {
            return substr($webPath, 0, strpos($webPath, '/tmp/') + 5);
        }
        return null;
    }

    abstract protected function getTempDirectory(): string;

    abstract protected function webPathToAbsolutePath(string $webPath): string;

    abstract protected function absolutePathToWebPath(string $absolutePath): string;
}