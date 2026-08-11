<?php

declare(strict_types=1);

class TempFileCleaner
{
    public function __construct(
        private DirectoryManager $directoryManager,
        private FileOperationsManager $fileManager,
        private int $defaultMaxAge = 3600, // 1 hour default
    ) {
    }

    public function clean(string $directory, ?int $maxAge = null): int
    {
        $ageLimit = $maxAge ?? $this->defaultMaxAge;
        $cleaned = 0;
        $now = time();

        if (!$this->directoryManager->exists($directory)) {
            return 0;
        }

        $files = $this->directoryManager->list($directory, true);

        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }
            if (str_ends_with($file->getPathname(), '.lock')) {
                continue;
            }

            if (($now - $file->getMTime()) > $ageLimit) {
                try {
                    $this->fileManager->delete($file->getPathname());
                    $lockFile = $file->getPathname() . '.lock';
                    if (file_exists($lockFile)) {
                        unlink($lockFile);
                    }
                    $cleaned++;
                } catch (FileException $e) {
                    error_log('TempFileCleaner: ' . $e->getMessage());
                }
            }
        }

        return $cleaned;
    }

    /**
     * Clean up specific files (used after permanent storage).
     */
    public function cleanSpecificFiles(array $filePaths): int
    {
        $cleaned = 0;
        foreach ($filePaths as $path) {
            if (file_exists($path)) {
                if (@unlink($path)) {
                    $cleaned++;
                }
                // Clean up .lock file
                $lockFile = $path . '.lock';
                if (file_exists($lockFile)) {
                    @unlink($lockFile);
                }
            }
        }
        return $cleaned;
    }
}