<?php

declare(strict_types=1);

trait FileCleanupTrait
{
    protected TempFileCleaner $tempFileCleaner;
    protected array $protectedFiles = [];

    /**
     * Clean up specific files that were just processed
     * Called after makePermanent() succeeds.
     */
    public function cleanupProcessedFiles(array $filePaths): int
    {
        if (!isset($this->tempFileCleaner)) {
            return $this->manualCleanupProcessedFiles($filePaths);
        }

        return $this->tempFileCleaner->cleanSpecificFiles($filePaths);
    }

    /**
     * Clean up orphaned temp files (no longer referenced)
     * Called after all components are processed.
     */
    public function cleanupOrphanedFiles(array $activePaths = []): int
    {
        if (!isset($this->tempFileCleaner)) {
            return $this->manualCleanupOrphanedFiles($activePaths);
        }

        $tempDir = $this->getTempDirectory();
        $allFiles = $this->getAllTempFiles($tempDir);
        $cleaned = 0;

        foreach ($allFiles as $file) {
            $webPath = $this->absolutePathToWebPath($file);

            // Skip if file is actively referenced
            if (in_array($webPath, $activePaths)) {
                continue;
            }

            // Skip if file is protected by this component
            if (isset($this->protectedFiles[$file])) {
                continue;
            }

            // File is orphaned - safe to delete
            if ($this->tempFileCleaner->cleanSpecificFiles([$file]) > 0) {
                $cleaned++;
            }
        }

        return $cleaned;
    }

    /**
     * Clean up aged files (safety net - call via cron).
     */
    public function cleanupAgedFiles(int $maxAgeSeconds = 3600): int
    {
        if (!isset($this->tempFileCleaner)) {
            return $this->manualCleanupAgedFiles($maxAgeSeconds);
        }

        return $this->tempFileCleaner->clean($this->getTempDirectory(), $maxAgeSeconds);
    }

    /**
     * Mark a file as protected (should not be cleaned).
     */
    protected function markFileAsProtected(string $absolutePath): void
    {
        $this->protectedFiles[$absolutePath] = true;

        // Create a marker file for cross-request protection
        $markerFile = $absolutePath . '.protected';
        file_put_contents($markerFile, session_id());
    }

    /**
     * Check if a file is protected.
     */
    protected function isFileProtected(string $absolutePath): bool
    {
        // Check in-memory protection
        if (isset($this->protectedFiles[$absolutePath])) {
            return true;
        }

        // Check marker file (cross-request)
        $markerFile = $absolutePath . '.protected';
        if (file_exists($markerFile)) {
            $sessionId = file_get_contents($markerFile);
            if ($sessionId === session_id()) {
                return true;
            }
            // Expired marker (older than 1 hour)
            if ((time() - filemtime($markerFile)) < 3600) {
                return true;
            }
            unlink($markerFile); // Clean expired marker
        }

        return false;
    }

    /**
     * These methods must be implemented by the class using this trait.
     */
    abstract protected function getTempDirectory(): string;

    abstract protected function absolutePathToWebPath(string $absolutePath): string;

    /**
     * Fallback manual cleanup methods.
     */
    private function manualCleanupProcessedFiles(array $filePaths): int
    {
        $cleaned = 0;
        foreach ($filePaths as $path) {
            if (file_exists($path) && @unlink($path)) {
                $cleaned++;
            }
        }
        return $cleaned;
    }

    private function manualCleanupOrphanedFiles(array $activePaths): int
    {
        $cleaned = 0;
        $tempDir = $this->getTempDirectory();
        $allFiles = glob($tempDir . '*');

        foreach ($allFiles as $file) {
            if (is_dir($file)) {
                continue;
            }

            $webPath = $this->absolutePathToWebPath($file);
            if (!in_array($webPath, $activePaths) && !$this->isFileProtected($file)) {
                if (@unlink($file)) {
                    $cleaned++;
                }
            }
        }

        return $cleaned;
    }

    private function manualCleanupAgedFiles(int $maxAgeSeconds): int
    {
        $cleaned = 0;
        $now = time();
        $tempDir = $this->getTempDirectory();

        foreach (glob($tempDir . '*') as $file) {
            if (is_file($file) && ($now - filemtime($file)) > $maxAgeSeconds) {
                if (@unlink($file)) {
                    $cleaned++;
                }
            }
        }

        return $cleaned;
    }

    private function getAllTempFiles(string $tempDir): array
    {
        $files = [];
        foreach (glob($tempDir . '*') as $item) {
            if (is_file($item)) {
                $files[] = $item;
            }
        }
        return $files;
    }
}