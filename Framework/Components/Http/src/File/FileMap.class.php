<?php

declare(strict_types=1);
class FileMap
{
    private const array FILE_KEYS = ['error', 'name', 'size', 'tmp_name', 'type'];
    /**
     * @var array<string,FileUpload|FileUpload[]>
     */
    private array $files;

    public function __construct(array $files)
    {
        $this->sanitizeFiles($files);
    }

    /**
     * Get the value of files.
     *
     * @return  array<string,FileUpload|FileUpload[]>
     */
    public function all()
    {
        return $this->files;
    }

    public function hasFile(string $name) : bool
    {
        return array_key_exists($name, $this->files);
    }

    /**
     * @param string $name
     * @param int|null $index
     * @return FileUpload[]|FileUpload|null
     */
    public function getFile(string $name, int|null $index) : array|FileUpload|null
    {
        if (! $this->hasFile($name)) {
            return null;
        }
        $value = $this->files[$name];
        if ($index !== null && is_array($value) && array_key_exists($index, $value)) {
            return $value[$index];
        }
        return $value;
    }

    public function getName() : string|null
    {
        if (! empty($this->files)) {
            return key($this->files);
        }
        return null;
    }

    private function sanitizeFiles(array $files)
    {
        $this->files = [];
        foreach ($files as $key => $file) {
            unset($file['full_path']);
            if (! ArrayUtils::doArraysHasTheSameValues(self::FILE_KEYS, array_keys($file), true)) {
                continue;
            }
            if (is_array($file['name'])) {
                $this->files[$key] = $this->sanitizeFileArray($file);
            } else {
                $this->files[$key] = $this->sanitizeSingleFile($file);
            }
        }
    }

    private function sanitizeSingleFile(array $file) : FileUpload
    {
        return new FileUpload(
            $file['tmp_name'],
            $file['name'],
            $file['type'],
            (int) $file['error']
        );
    }

    private function sanitizeFileArray(array $arrayFile) : array
    {
        $files = [];
        foreach ($arrayFile as $key => $values) {
            foreach ($values as $index => $value) {
                $files[$index][$key] = $value;
            }
        }
        return array_map(fn ($arrayFile) => $this->sanitizeSingleFile($arrayFile), $files);
    }
}