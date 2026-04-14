<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class OrphanFileCleanupService
{
    private array $excludedFiles = [
        '.htaccess',
        '.gitkeep',
        '.gitignore',
        'index.html',
        'index.php',
    ];

    public function __construct(
        private OrphanFileFinderService $orphanFinder,
        private FileOperationsManager $fileOperations,
        private LoggerInterface $logger,
    ) {
    }

    public function cleanupOrphanFiles(string $uploadsDir, array $options = []): CleanupResult
    {
        $options = array_merge([
            'dry_run' => true,
            'max_age_days' => 30,
            'clean_temp_files' => true,
            'temp_max_age_hours' => 1,
        ], $options);

        $result = new CleanupResult();

        // 1. Clean temp files (always safe to clean)
        if ($options['clean_temp_files']) {
            $tempResult = $this->cleanupTempFiles($uploadsDir, $options);
            $result->merge($tempResult);
        }

        // 2. Clean orphan files (using OrphanFileFinderService)
        $orphanResult = $this->cleanupUploadOrphanFiles($uploadsDir, $options);
        $result->merge($orphanResult);

        return $result;
    }

    public function getUploadStats(string $uploadsDir): array
    {
        $stats = [
            'total_size' => 0,
            'total_files' => 0,
            'temp_files' => 0,
            'temp_size' => 0,
            'orphan_candidates' => 0,
            'orphan_size' => 0,
        ];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($uploadsDir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY,
            );

            $orphans = $this->orphanFinder->findOrphanFiles($uploadsDir);
            $orphanPaths = array_column($orphans, 'relative_path');

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    continue;
                }

                $filename = $file->getFilename();

                // Skip excluded files from stats
                if ($this->isExcludedFile($filename)) {
                    continue;
                }

                $size = $file->getSize();
                $relativePath = $this->getRelativePath($uploadsDir, $file->getPathname());

                $stats['total_size'] += $size;
                $stats['total_files']++;

                // Check if temp file
                if (strpos($relativePath, 'temp/') === 0) {
                    $stats['temp_files']++;
                    $stats['temp_size'] += $size;
                }
                // Check if orphan candidate
                elseif (in_array($relativePath, $orphanPaths, true)) {
                    $stats['orphan_candidates']++;
                    $stats['orphan_size'] += $size;
                }
            }
        } catch (Exception $e) {
            $this->logger->error('Error getting upload stats', [
                'directory' => $uploadsDir,
                'error' => $e->getMessage(),
            ]);
        }

        return $stats;
    }

    private function cleanupTempFiles(string $uploadsDir, array $options): CleanupResult
    {
        $result = new CleanupResult();
        $tempDir = rtrim($uploadsDir, '/') . '/temp/';

        if (!is_dir($tempDir)) {
            return $result;
        }

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($tempDir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY,
            );

            $cutoffTime = time() - ($options['temp_max_age_hours'] * 3600);

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    continue;
                }

                $filename = $file->getFilename();

                // Skip excluded files
                if ($this->isExcludedFile($filename)) {
                    continue;
                }

                if ($file->getMTime() < $cutoffTime) {
                    $fileInfo = [
                        'path' => $file->getPathname(),
                        'relative_path' => $this->getRelativePath($uploadsDir, $file->getPathname()),
                        'size' => $file->getSize(),
                        'modified_at' => $file->getMTime(),
                        'age_hours' => round((time() - $file->getMTime()) / 3600, 1),
                        'type' => 'temp',
                    ];

                    if ($options['dry_run']) {
                        $result->addCandidate($fileInfo);
                    } else {
                        try {
                            $this->fileOperations->delete($file->getPathname());
                            $result->addDeleted($fileInfo);
                            $this->logger->info('Deleted temp file', $fileInfo);
                        } catch (Exception $e) {
                            $result->addFailed($fileInfo, $e->getMessage());
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->error('Error cleaning temp files', [
                'directory' => $tempDir,
                'error' => $e->getMessage(),
            ]);
        }

        return $result;
    }

    private function cleanupUploadOrphanFiles(string $uploadsDir, array $options): CleanupResult
    {
        $result = new CleanupResult();

        // Use OrphanFileFinderService to find orphans
        $orphans = $this->orphanFinder->findOrphanFiles($uploadsDir);

        $cutoffTime = time() - ($options['max_age_days'] * 86400);

        foreach ($orphans as $orphan) {
            $filename = basename($orphan['path']);

            // Skip excluded files - DON'T add them as candidates at all
            if ($this->isExcludedFile($filename)) {
                continue; // Skip completely
            }

            // Make sure modified_at is set
            if (!isset($orphan['modified_at']) && file_exists($orphan['path'])) {
                $orphan['modified_at'] = filemtime($orphan['path']) ?: time();
            }

            // Only delete files older than max_age_days
            $shouldDelete = true;
            if (isset($orphan['modified_at'])) {
                $shouldDelete = $orphan['modified_at'] < $cutoffTime;
            }

            if ($shouldDelete) {
                $fileInfo = $orphan;
                // Ensure all fields are set
                if (!isset($fileInfo['modified_at'])) {
                    $fileInfo['modified_at'] = time();
                }
                $fileInfo['age_days'] = isset($fileInfo['modified_at']) ?
                    round((time() - $fileInfo['modified_at']) / 86400, 1) : 'unknown';
                $fileInfo['type'] = 'orphan';

                if ($options['dry_run']) {
                    $result->addCandidate($fileInfo);
                } else {
                    try {
                        $this->fileOperations->delete($orphan['path']);
                        $result->addDeleted($fileInfo);
                        $this->logger->info('Deleted orphan file', $fileInfo);
                    } catch (Exception $e) {
                        $result->addFailed($fileInfo, $e->getMessage());
                    }
                }
            }
            // Don't add skipped files as candidates at all
        }

        return $result;
    }

    private function isExcludedFile(string $filename): bool
    {
        return in_array($filename, $this->excludedFiles, true);
    }

    private function getRelativePath(string $baseDir, string $fullPath): string
    {
        $baseDir = rtrim($baseDir, '/') . '/';
        if (strpos($fullPath, $baseDir) === 0) {
            return substr($fullPath, strlen($baseDir));
        }
        return $fullPath;
    }
}
