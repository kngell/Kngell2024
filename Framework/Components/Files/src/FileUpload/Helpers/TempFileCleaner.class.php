<?php

declare(strict_types=1);

class TempFileCleaner
{
    private const int TEMP_FILE_MAX_AGE = 3600;

    public function cleanupDirectory(string $directory): int
    {
        $cleaned = 0;
        $now = time();

        if (!is_dir($directory)) {
            return 0;
        }

        foreach (glob($directory . '*') as $file) {
            if (is_file($file) && ($now - filemtime($file)) > self::TEMP_FILE_MAX_AGE) {
                if (unlink($file)) {
                    $cleaned++;
                }
            }
        }

        return $cleaned;
    }

    public function cleanupMultipleDirectories(array $directories): array
    {
        $results = [];
        foreach ($directories as $directory) {
            $results[$directory] = $this->cleanupDirectory($directory);
        }
        return $results;
    }
}