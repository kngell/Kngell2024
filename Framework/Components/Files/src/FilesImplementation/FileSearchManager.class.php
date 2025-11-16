<?php

declare(strict_types=1);

class FileSearchManager implements FileSearchInterface
{
    /**
     * Find a single file with exact filename matching
     * Can optionally restrict to directory path.
     */
    public function findFile(
        string $directory,
        string $filename,
        ?string $inDirectoryPath = null,
    ): ?FileInformation {
        if (!is_dir($directory)) {
            return null;
        }

        $matches = $this->findFiles($directory, $filename, $inDirectoryPath);

        if (empty($matches)) {
            return null;
        }

        if (count($matches) > 1) {
            throw new AmbiguousFileException(
                "Multiple files match '{$filename}'" .
                ($inDirectoryPath ? " in path '{$inDirectoryPath}'" : '') .
                '. Found: ' . implode(', ', array_map(fn ($f) => $f->getPathname(), $matches)),
            );
        }

        return $matches[0];
    }

    /**
     * Find view file - specialized version for view resolution.
     */
    public function findViewFile(string $viewsDirectory, string $viewPath): FileInformation
    {
        if (!is_dir($viewsDirectory)) {
            throw new ViewNotFoundException("Views directory not found: {$viewsDirectory}");
        }

        $pathInfo = $this->parseViewPath($viewPath);
        $searchFilename = $pathInfo['filename'] . '.php';
        $searchDirPath = $pathInfo['dirPath'];

        $file = $this->findFile($viewsDirectory, $searchFilename, $searchDirPath);

        if (!$file) {
            throw new ViewNotFoundException("View not found: {$viewPath}");
        }

        return $file;
    }

    /**
     * Find files by pattern (original functionality).
     */
    public function findFilesByPattern(string $directory, string $pattern): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && fnmatch($pattern, $file->getFilename())) {
                $files[] = new FileInformation($file->getPathname());
            }
        }

        return $files;
    }

    public function findFilesByExtension(string $directory, string $extension): array
    {
        $extension = ltrim($extension, '.');
        $pattern = '*.' . $extension;
        return $this->findFilesByPattern($directory, $pattern);
    }

    public function findFilesByMimeType(string $directory, string $mimeType): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $fileInfo = new FileInformation($file->getPathname());
                if ($fileInfo->getMimeType() === $mimeType) {
                    $files[] = $fileInfo;
                }
            }
        }

        return $files;
    }

    /**
     * Get all available files (for debugging/listing).
     */
    public function getAllFiles(string $directory, ?string $extension = null): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                if ($extension && $file->getExtension() !== $extension) {
                    continue;
                }
                $relativePath = $this->getRelativePath($directory, $file->getPathname());
                $files[$relativePath] = new FileInformation($file->getPathname());
            }
        }

        return $files;
    }

    /**
     * Get all available views (specialized version).
     */
    public function getAllAvailableViews(string $viewsDirectory): array
    {
        $allFiles = $this->getAllFiles($viewsDirectory, 'php');
        $views = [];

        foreach ($allFiles as $relativePath => $fileInfo) {
            $route = $this->filePathToRoute($relativePath);
            if ($route) {
                $views[$route] = $fileInfo;
            }
        }

        return $views;
    }

    /**
     * Find multiple files with filtering options.
     */
    private function findFiles(
        string $directory,
        string $filename,
        ?string $inDirectoryPath = null,
    ): array {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        $matches = [];

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            // Check filename match
            if ($file->getFilename() !== $filename) {
                continue;
            }

            $relativePath = $this->getRelativePath($directory, $file->getPathname());
            $relativeDir = dirname($relativePath);

            // If no directory path specified, include all matches
            if (empty($inDirectoryPath)) {
                $matches[] = new FileInformation($file->getPathname());
                continue;
            }

            // Check if file is in the requested directory path
            if ($this->pathEndsWith($relativeDir, $inDirectoryPath)) {
                $matches[] = new FileInformation($file->getPathname());
            }
        }

        return $matches;
    }

    /**
     * Parse view path for view resolution.
     */
    private function parseViewPath(string $viewPath): array
    {
        // Remove any null bytes (security)
        $viewPath = str_replace("\0", '', $viewPath);

        // Normalize slashes
        $viewPath = str_replace('\\', '/', $viewPath);
        $viewPath = trim($viewPath, '/');

        // Validate path doesn't contain directory traversal
        if (preg_match('/(\.\.\/|\.\.\\\)/', $viewPath)) {
            throw new InvalidArgumentException('Invalid view path: directory traversal detected');
        }

        // Split into segments
        $segments = explode('/', $viewPath);

        if (empty($segments)) {
            throw new InvalidArgumentException('View path cannot be empty');
        }

        // Last segment is always the filename (without .php extension)
        $filename = array_pop($segments);

        // Remaining segments form the directory path
        $dirPath = implode('/', $segments);

        return [
            'filename' => $filename,
            'dirPath' => $dirPath,
        ];
    }

    private function pathEndsWith(string $fullPath, string $searchPath): bool
    {
        if (empty($searchPath)) {
            return true;
        }

        $fullPath = trim($fullPath, '/');
        $searchPath = trim($searchPath, '/');

        return $fullPath === $searchPath || str_ends_with($fullPath, '/' . $searchPath);
    }

    private function getRelativePath(string $baseDir, string $fullPath): string
    {
        $baseDir = rtrim($baseDir, '/') . '/';
        return str_replace($baseDir, '', $fullPath);
    }

    private function filePathToRoute(string $filePath): string
    {
        // Remove .php extension
        $route = preg_replace('/\.php$/', '', $filePath);

        // Remove /index from the end
        $route = preg_replace('/\/index$/', '', $route);

        return trim($route, '/');
    }

    /**
     * Static helper for backward compatibility.
     */
    public static function get(string $directory, string $filename): string
    {
        $instance = new self();
        $file = $instance->findFile($directory, $filename);

        if (!$file) {
            throw new RuntimeException("File not found: {$filename} in {$directory}");
        }

        return $file->getPathname();
    }
}