<?php

declare(strict_types=1);

class FileSystem extends AbstractFiles implements FilesSystemInterface
{
    private string $rootPath = ROOT_DIR . DS;

    public function readFile(string $file): string
    {
        try {
            return file_get_contents($file);
        } catch (FilesException $th) {
            throw new FilesException($th->getMessage(), $th->getCode());
        }
    }

    public function createDirectory(string $folder): bool
    {
        try {
            $path = realpath($folder);
            if ($path !== false && is_dir($path)) {
                return true;
            } else {
                parent::createDirectoryRecursively($folder);
                return true;
            }
        } catch (FileSystemManagementException $th) {
            throw new FileSystemManagementException($th->getMessage(), $th->getCode());
        }
        // }
        // return false;
    }
    // public function createDirectory(string $directory): void
    // {
    //     if (! is_dir($directory)) {
    //         throw new FilesException(sprintf('Directory %s doest not exist', $directory));
    //     }
    //     if (! file_exists($directory)) {
    //         if (! @mkdir($directory, 0777, true)) {
    //             throw new FilesException(sprintf('Unable to create directory %s', $directory));
    //         }
    //     }
    // }

    public function get(string $folder, string $file = ''): mixed
    {
        $folder = $this->folder($folder);
        $file = file_exists($folder . $file) ? [$folder . $file] : $this->search_file($folder, $file);
        if (isset($file) && count($file) === 1) {
            $file = current($file);
            $infos = pathinfo($file);

            return match ($infos['extension']) {
                'json' => json_decode(file_get_contents($file), true),
                'php' => $file,
                'js' => parent::getFileContents($file)
            };
        }
        return false;
    }

    public function checkWritable(string $directory, bool $autoupload = false) : string
    {
        if (is_dir($directory) && is_writable($directory)) {
            return $directory;
        }
        if (is_dir($directory) && ! is_writable($directory)) {
            if ($autoupload) {
                chmod($directory, 0777);
            } else {
                throw new FilesException(sprintf('The Directory"%s" exists but is not writable. you can create a directory when it doesn\'t exist', $directory));
            }
        }
        return $directory;
    }

    public function listAllFiles(string $folder) : array|bool
    {
        $folder = $this->folder($folder);
        $fileList = $this->fileListFromFolder($folder);
        if ($fileList) {
            return array_diff($fileList, ['.', '..']);
        }
        return false;
    }

    public function files_model_diff(array $clientAry, array $dbAry): array
    {
        if (isset($clientAry) && isset($dbAry)) {
            foreach ($clientAry as $cm) {
                if (null != $cm) {
                    foreach ($dbAry as $key => $bm) {
                        if ($cm == $bm) {
                            unset($dbAry[$key]);
                        }
                    }
                }
            }
            return array_values($dbAry);
        }
    }

    public function fileAryFromModel(Model $media): array
    {
        return array_map(function ($m) {
            return basename(unserialize($m->{$m->get_media()})[0]);
        }, $media->get_results());
    }

    public function getFileModel(string $url, Model $model): ?Model
    {
        if ($model->count() > 0) {
            $m = current(array_filter($model->get_results(), function ($m) use ($url) {
                if (basename(unserialize($m->fileUrl)[0]) == $url) {
                    return $m;
                }
            }));
            return ! is_object($m) ? null : $m;
        }
    }

    public function urlsToRemove(array $imgAry, Model $m): array
    {
        if ($m->count() > 0 && isset($imgAry)) {
            $dbUslrsModel = [];
            foreach ($imgAry as $url) {
                $dbUslrsModel[] = $this->getFileModel($url, $m);
            }
            return $this->files_model_diff($dbUslrsModel, $m->get_results());
        }
        return [];
    }

    public function cleanDbFilesUrls(array $clientAry = [], ?Model $m = null): bool
    {
        try {
            $urlToRemove = $this->urlsToRemove(! empty($clientAry) ? $clientAry : [], $m);
            if (isset($urlToRemove) && is_array($urlToRemove) && ! empty($urlToRemove)) {
                $filesToRemove = [];
                foreach ($urlToRemove as $m) {
                    if ($m->delete()) {
                        $filesToRemove[] = $m;
                    }
                }
                return $this->cleanFilesSystemUrls($filesToRemove);
            }
            return true;
        } catch (\Throwable $th) {
            throw new FileSystemManagementException('Impossible de supprimer les fichiers! ' . $th->getMessage(), $th->getCode());
        }
        return false;
    }

    /**
     * cleanFilesSystemUrls
     * ===========================================================================.
     * @param array $urlsAry
     * @param string $table
     * @return mixed
     */
    public function cleanFilesSystemUrls(array $urlsAry = []): mixed
    {
        try {
            if (! empty($urlsAry)) {
                foreach ($urlsAry as $m) {
                    $fileToRemove = unserialize($m->{$m->get_media()});
                    if ($fileToRemove != '') {
                        $this->removeFile($fileToRemove, $m);
                    }
                }
            }
            return true;
        } catch (\Throwable $th) {
            throw new FileSystemManagementException('Impossible de supprimer les fichiers! ' . $th->getMessage(), $th->getCode());
        }
    }

    public function removeFile(string $folder, string $file) : bool
    {
        $file = $this->folder($folder) . $file;
        if (file_exists($file)) {
            unlink($file);
            return true;
        }
        return false;
    }

    public function search_file(string $folder, ?string $file_to_search = null, ?string $subFolder = '', array &$results = []): array|bool
    {
        $folder = $this->folder($folder);
        $files = ($folder !== false && is_dir($folder)) ? scandir($folder) : false;
        if ($files) {
            foreach ($files as $key => $value) {
                $path = realpath($folder . DS . $value);
                if (! is_dir($path)) {
                    if ($file_to_search == $value) {
                        if ($subFolder == null) {
                            $results[] = $path;
                        } elseif (basename(dirname($path)) == $subFolder) {
                            $results[] = $path;
                        }
                    }
                } elseif ($value != '.' && $value != '..') {
                    $this->search_file($path, $file_to_search, $subFolder, $results);
                    if ($file_to_search == $value) {
                        if ($subFolder == null) {
                            $results[] = $path;
                        } elseif (basename(dirname($path)) == $subFolder) {
                            $results[] = $path;
                        }
                    }
                }
            }
            return $results;
        }
        return false;
    }

    private function folder(string $folder) : string
    {
        return str_starts_with($folder, $this->rootPath) ? $folder : $this->rootPath . $folder . DS;
    }

    private function fileListFromFolder(string $folder): array|bool
    {
        return ($folder !== false && is_dir($folder)) ? scandir($folder) : false;
    }
}