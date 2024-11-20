<?php

declare(strict_types=1);
class FileManager
{
    public static function searchFile(string $directory, string $fileToSearch) : string|bool
    {
        list($directory, $fileToSearch) = self::fileTosearch($directory, $fileToSearch);
        $iterator = new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS);
        $ItSearch = new RecursiveIteratorIterator($iterator);
        foreach ($ItSearch as $file) {
            if ($file->isFile() && str_contains($file->getFilename(), $fileToSearch)) {
                return $file->getPathName();
            }
        }
        return false;
    }

    private static function fileTosearch(string $directory, string $fileToSearch) : array
    {
        $fileParts = explode(DS, $fileToSearch);
        if (count($fileParts) > 1) {
            $fileToSearch = $fileParts[array_key_last($fileParts)];
            unset($fileParts[array_key_last($fileParts)]);
            $fileParts = array_values($fileParts);
            foreach ($fileParts as $key => $part) {
                $directory .= DS . $part;
            }
        }
        return [$directory, $fileToSearch];
    }
}