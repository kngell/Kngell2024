<?php

declare(strict_types=1);
interface FilesSystemInterface
{
    public function get(string $folder, string $file = '') : mixed;

    public function createDirectory(string $folder) : bool;

    public function search_file(string $folder, ?string $file_to_search = null, ?string $subFolder = '', array &$results = []) : array|bool;

    public function checkWritable(string $directory, bool $autoupload = false) : string;

    public function removeFile(string $folder, string $file) : bool;

    public function listAllFiles(string $folder) : array|bool;
}