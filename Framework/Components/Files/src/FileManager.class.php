<?php

declare(strict_types=1);

class FileManager implements FileSystemInterface, FileContentInterface, FileOperationsInterface, FileSearchInterface
{
    public function __construct(
        private FileContentInterface $contentManager,
        private DirectoryInterface $directoryManager,
        private FileOperationsInterface $operationsManager,
        private FileSearchInterface $searchManager,
    ) {
    }

    public function findViewFile(string $viewsDirectory, string $viewPath): FileInformation
    {
    }

    public function getAllFiles(string $directory, ?string $extension = null): array
    {
    }

    public function getAllAvailableViews(string $viewsDirectory): array
    {
    }

    // FileSystemInterface
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    public function isReadable(string $path): bool
    {
        return is_readable($path);
    }

    public function isWritable(string $path): bool
    {
        return is_writable($path);
    }

    public function getPermissions(string $path): int
    {
        return fileperms($path) & 0777;
    }

    // FileContentInterface
    public function read(string $filePath): string
    {
        return $this->contentManager->read($filePath);
    }

    public function write(string $filePath, string $content, bool $append = false): void
    {
        $this->contentManager->write($filePath, $content, $append);
    }

    public function getStream(string $filePath, string $mode = 'r')
    {
        return $this->contentManager->getStream($filePath, $mode);
    }

    public function putStream(string $filePath, $stream): void
    {
        $this->contentManager->putStream($filePath, $stream);
    }

    // FileOperationsInterface
    public function copy(string $source, string $destination): void
    {
        $this->operationsManager->copy($source, $destination);
    }

    public function move(string $source, string $destination): void
    {
        $this->operationsManager->move($source, $destination);
    }

    public function delete(string $path): void
    {
        $this->operationsManager->delete($path);
    }

    public function getSize(string $path): int
    {
        return $this->operationsManager->getSize($path);
    }

    public function getChecksum(string $path, string $algorithm = 'md5'): string
    {
        return $this->operationsManager->getChecksum($path, $algorithm);
    }

    public function touch(string $path, ?int $time = null, ?int $atime = null): void
    {
        $this->operationsManager->touch($path, $time, $atime);
    }

    // FileSearchInterface
    public function findFile(
        string $directory,
        string $filename,
        ?string $inDirectoryPath = null,
    ): ?FileInformation {
        return $this->searchManager->findFile($directory, $filename);
    }

    public function findFilesByPattern(string $directory, string $pattern): array
    {
        return $this->searchManager->findFilesByPattern($directory, $pattern);
    }

    public function findFilesByExtension(string $directory, string $extension): array
    {
        return $this->searchManager->findFilesByExtension($directory, $extension);
    }

    public function findFilesByMimeType(string $directory, string $mimeType): array
    {
        return $this->searchManager->findFilesByMimeType($directory, $mimeType);
    }

    // Convenience methods
    public function getHumanReadableSize(string $path): string
    {
        $bytes = $this->getSize($path);
        return $this->formatBytes($bytes);
    }

    public function ensureDirectoryExists(string $path, int $permissions = 0755): void
    {
        if (!$this->exists($path)) {
            $this->directoryManager->create($path, $permissions);
        }
    }

    public function getFileInformation(string $path): FileInformation
    {
        if (!$this->exists($path)) {
            throw new FileException("Path does not exist: {$path}");
        }
        return new FileInformation($path);
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    // Static factory for convenience
    public static function create(): self
    {
        $contentManager = new FileContentManager();
        $directoryManager = new DirectoryManager();
        $operationsManager = new FileOperationsManager($directoryManager);
        $searchManager = new FileSearchManager();

        return new self($contentManager, $directoryManager, $operationsManager, $searchManager);
    }
}