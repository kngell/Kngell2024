<?php

declare(strict_types=1);
class DirectoryManager implements DirectoryInterface, FileSystemInterface
{
    public function create(string $path, int $permissions = 0755): void
    {
        if (!is_dir($path) && !mkdir($path, $permissions, true)) {
            throw new FileException("Cannot create directory: {$path}");
        }
        chmod($path, $permissions);
    }

    public function list(string $path, bool $recursive = false): array
    {
        if (!is_dir($path)) {
            throw new FileException("Directory does not exist: {$path}");
        }

        $items = [];

        if ($recursive) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST,
            );
        } else {
            $iterator = new DirectoryIterator($path);
        }

        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }
            $items[] = new FileInformation($item->getPathname());
        }

        return $items;
    }

    public function delete(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }

        $items = array_diff(scandir($path), ['.', '..']);
        foreach ($items as $item) {
            $itemPath = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                $this->delete($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        if (!rmdir($path)) {
            throw new FileException("Cannot delete directory: {$path}");
        }
    }

    public function isEmpty(string $path): bool
    {
        if (!is_dir($path)) {
            return true;
        }

        $items = scandir($path);
        return $items && count($items) <= 2;
    }

    public function getFileCount(string $path): int
    {
        $count = 0;
        $items = $this->list($path, true);

        foreach ($items as $item) {
            if ($item->isFile()) {
                $count++;
            }
        }

        return $count;
    }

    public function getDirectoryCount(string $path): int
    {
        $count = 0;
        $items = $this->list($path, true);

        foreach ($items as $item) {
            if ($item->isDir()) {
                $count++;
            }
        }

        return $count;
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