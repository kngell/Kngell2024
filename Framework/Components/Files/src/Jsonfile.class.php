<?php

declare(strict_types=1);
class JsonFile
{
    private string $content;
    private bool $isLoaded = false;
    private string $file;

    public function __construct(string $file)
    {
        if (file_exists($file)) {
            $this->content = $this->getContent($file);
        } elseif (filter_var($file, FILTER_VALIDATE_URL)) {
            $this->content = $this->getContent($file);
        } else {
            json_decode($file, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Invalid JSON string provided: {$file}");
            }
            $this->content = $file;
            $this->isLoaded = true;
        }
    }

    public function getContentAsArray() : array
    {
        if (empty($this->content)) {
            throw new RuntimeException("Content is empty for file: {$this->file}");
        }
        if (! $this->isLoaded) {
            throw new RuntimeException("Content not loaded from file: {$this->file}");
        }
        return json_decode($this->content, true);
    }

    public function getContentAsJson() : string
    {
        if (! $this->isLoaded) {
            throw new RuntimeException("Content not loaded from file: {$this->file}");
        }
        return json_encode($this->content, JSON_PRETTY_PRINT);
    }

    private function getContent(string $file) : string
    {
        $content = file_get_contents($file);
        if ($content === false) {
            throw new RuntimeException("Failed to read file or url: {$file}");
        }
        $this->isLoaded = true;
        return $content;
    }
}