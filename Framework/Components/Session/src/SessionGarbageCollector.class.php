<?php

declare(strict_types=1);

class SessionGarbageCollector
{
    private string $sessionPath;
    private int $maxLifetime;

    public function __construct(string $sessionPath, int $maxLifetime = 3600)
    {
        $this->sessionPath = $sessionPath;
        $this->maxLifetime = $maxLifetime;
    }

    /**
     * Clean up problematic sessions that could cause crashes.
     */
    public function cleanupProblematicSessions(): int
    {
        $files = $this->getSessionFiles();
        $deleted = 0;
        $currentTime = time();

        foreach ($files as $file) {
            if ($this->isProblematicSessionFile($file, $currentTime)) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Get session file statistics.
     */
    public function getSessionStats(): array
    {
        $files = $this->getSessionFiles();
        $stats = [
            'total' => count($files),
            'problematic' => 0,
            'large_files' => 0,
            'empty_files' => 0,
        ];

        foreach ($files as $file) {
            if ($this->isProblematicSessionFile($file, time())) {
                $stats['problematic']++;
            }

            $size = filesize($file);
            if ($size > 1024 * 1024) {
                $stats['large_files']++;
            }
            if ($size === 0) {
                $stats['empty_files']++;
            }
        }

        return $stats;
    }

    /**
     * Get all session files.
     */
    public function getSessionFiles(): array
    {
        $pattern = $this->sessionPath . '/sess_*';
        $files = glob($pattern);
        return $files ?: [];
    }

    /**
     * Run regular garbage collection.
     */
    public function collectGarbage(): int
    {
        return $this->cleanupProblematicSessions();
    }

    /**
     * Check if a session file is problematic.
     */
    private function isProblematicSessionFile(string $filePath, int $currentTime): bool
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $fileSize = filesize($filePath);
        $fileTime = filemtime($filePath);
        $fileAge = $currentTime - $fileTime;

        // 1. Very large session files (> 1MB)
        if ($fileSize > 1024 * 1024) {
            return true;
        }

        // 2. Empty session files
        if ($fileSize === 0) {
            return true;
        }

        // 3. Very old session files
        if ($fileAge > $this->maxLifetime) {
            return true;
        }

        // 4. Check if file content is corrupted
        if ($this->isSessionFileCorrupted($filePath)) {
            return true;
        }

        return false;
    }

    /**
     * Check if session file content is corrupted.
     */
    private function isSessionFileCorrupted(string $filePath): bool
    {
        $content = file_get_contents($filePath);

        // Empty content
        if (empty($content)) {
            return true;
        }

        // Check for serialization errors in session data
        if (strpos($content, '|') !== false) {
            $parts = explode('|', $content, 2);
            if (count($parts) === 2) {
                // Try to unserialize the session data
                try {
                    unserialize($parts[1]);
                } catch (Exception $e) {
                    return true; // Serialization error = corrupted
                }
            }
        }

        // Very long lines might indicate corruption
        if (strlen($content) > 10000) {
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                if (strlen($line) > 1000) {
                    return true;
                }
            }
        }

        return false;
    }
}