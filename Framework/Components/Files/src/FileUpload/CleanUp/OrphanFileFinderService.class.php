<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

class OrphanFileFinderService
{
    public function __construct(
        private DatabaseFilePathService $databaseFilePaths,
        private ?LoggerInterface $logger = null,
    ) {
    }

    public function findOrphanFiles(string $directory): array
    {
        $validPaths = $this->databaseFilePaths->getValidFilePaths();
        $orphans = [];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY,
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    continue;
                }

                $relativePath = $this->getRelativePath($directory, $file->getPathname());

                // Skip temp files
                if (strpos($relativePath, 'temp/') === 0) {
                    continue;
                }

                // Normalize for comparison
                $normalizedPath = $this->normalizePath($relativePath);

                // Check if file exists in database
                if (!in_array($normalizedPath, $validPaths, true)) {
                    $orphans[] = [
                        'path' => $file->getPathname(),
                        'relative_path' => $relativePath,
                        'size' => $file->getSize(),
                        'modified_at' => $file->getMTime(),
                    ];
                }
            }
        } catch (Exception $e) {
            $this->logger?->error('Error finding orphan files', [
                'directory' => $directory,
                'error' => $e->getMessage(),
            ]);
        }

        return $orphans;
    }

    private function getRelativePath(string $baseDir, string $fullPath): string
    {
        $baseDir = rtrim($baseDir, '/') . '/';
        if (strpos($fullPath, $baseDir) === 0) {
            return substr($fullPath, strlen($baseDir));
        }
        return $fullPath;
    }

    private function normalizePath(string $path): string
    {
        return ltrim(str_replace('\\', '/', $path), '/');
    }
}
