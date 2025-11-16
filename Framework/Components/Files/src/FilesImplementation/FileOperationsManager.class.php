<?php

declare(strict_types=1);
class FileOperationsManager implements FileOperationsInterface, FileSystemInterface
{
    public function __construct(
        private DirectoryInterface $directoryManager,
    ) {
    }

    public function copy(string $source, string $destination): void
    {
        if (!file_exists($source)) {
            throw new FileException("Source file does not exist: {$source}");
        }

        $this->directoryManager->create(dirname($destination));

        if (!copy($source, $destination)) {
            throw new FileException("Cannot copy {$source} to {$destination}");
        }

        chmod($destination, 0644);
    }

    public function move(string $source, string $destination): void
    {
        if (!file_exists($source)) {
            throw new FileException("Source file does not exist: {$source}");
        }

        $this->directoryManager->create(dirname($destination));

        if (!rename($source, $destination)) {
            throw new FileException("Cannot move {$source} to {$destination}");
        }
    }

    public function delete(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (is_file($path)) {
            if (!unlink($path)) {
                throw new FileException("Cannot delete file: {$path}");
            }
        } else {
            $this->directoryManager->delete($path);
        }
    }

    public function getSize(string $path): int
    {
        if (!file_exists($path)) {
            throw new FileException("Path does not exist: {$path}");
        }

        if (is_file($path)) {
            $size = filesize($path);
            if ($size === false) {
                throw new FileException("Cannot get file size: {$path}");
            }
            return $size;
        }

        // For directories, calculate total size recursively
        $totalSize = 0;
        $items = $this->directoryManager->list($path, true);

        foreach ($items as $item) {
            if ($item->isFile()) {
                $totalSize += $item->getSize();
            }
        }

        return $totalSize;
    }

    public function getChecksum(string $path, string $algorithm = 'md5'): string
    {
        if (!file_exists($path) || !is_file($path)) {
            throw new FileException("File does not exist: {$path}");
        }

        $checksum = hash_file($algorithm, $path);
        if ($checksum === false) {
            throw new FileException("Cannot calculate checksum for: {$path}");
        }

        return $checksum;
    }

    public function touch(string $path, ?int $time = null, ?int $atime = null): void
    {
        $this->directoryManager->create(dirname($path));

        if ($time === null) {
            $time = time();
        }

        if (!touch($path, $time, $atime ?? $time)) {
            throw new FileException("Cannot touch file: {$path}");
        }
    }

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
}