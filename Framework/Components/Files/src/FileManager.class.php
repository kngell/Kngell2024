<?php

declare(strict_types=1);
class FileManager
{
    public static function get(string $directory, string $fileToSearch) : string|bool
    {
        $iterator = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
        $ItSearch = new RecursiveIteratorIterator($iterator);
        foreach ($ItSearch as $file) {
            $iteratorFile = $file->getBasename('.' . $file->getExtension());
            $finfo = pathinfo($fileToSearch);
            if ($file->isFile() && str_contains($file->getPathName(), $fileToSearch) && $iteratorFile === $finfo['filename']) {
                return $file->getPathName();
            }
        }
        return false;
    }

    public static function allFilePaths(string $directory) : array
    {
        $files = [];
        $iterator = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
        $ItSearch = new RecursiveIteratorIterator($iterator);
        foreach ($ItSearch as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathName();
            }
        }
        return $files;
    }

    public static function dirFilePaths(string $directory) : array
    {
        $files = [];
        $iterator = new DirectoryIterator($directory);
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathName();
            }
        }
        return $files;
    }

    public static function filePaths(string $directory) : array
    {
        $files = [];
        $handler = opendir($directory);
        if ($handler) {
            while (($file = readdir($handler)) !== false) {
                if ($file !== '.' && $file !== '..') {
                    $files[] = $directory . DS . $file;
                }
            }
            closedir($handler);
        }
        return $files;
    }

    public static function deleteFile(string $file) : bool
    {
        if (file_exists($file)) {
            return unlink($file);
        }
        return false;
    }

    public static function deleteDir(string $dir) : bool
    {
        if (! is_dir($dir)) {
            return false;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::deleteDir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    public static function createDir(string $dir) : bool
    {
        if (! is_dir($dir)) {
            return mkdir($dir, 0777, true);
        }
        return false;
    }

    public static function createFile(string $file) : bool
    {
        if (! file_exists($file)) {
            return touch($file);
        }
        return false;
    }

    public static function copyFile(string $source, string $dest) : bool
    {
        if (file_exists($source)) {
            return copy($source, $dest);
        }
        return false;
    }

    public static function moveFile(string $source, string $dest) : bool
    {
        if (file_exists($source)) {
            return rename($source, $dest);
        }
        return false;
    }

    public static function getFileName(string $file) : string
    {
        return pathinfo($file, PATHINFO_FILENAME);
    }

    public static function getFileExtension(string $file) : string
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    public static function getFileSize(string $file) : int
    {
        return filesize($file);
    }

    public static function getFileMimeType(string $file) : string
    {
        return mime_content_type($file);
    }

    public static function getFileInfo(string $file) : array
    {
        return pathinfo($file);
    }

    public static function getFileContent(string $file) : string|false
    {
        return file_get_contents($file);
    }

    public static function putFileContent(string $file, string $content) : bool
    {
        return file_put_contents($file, $content) !== false;
    }

    public static function appendFileContent(string $file, string $content) : bool
    {
        return file_put_contents($file, $content, FILE_APPEND) !== false;
    }
}