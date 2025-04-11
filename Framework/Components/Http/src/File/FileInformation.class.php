<?php

declare(strict_types=1);

class FileInformation extends SplFileInfo
{
    public function __construct(string $path)
    {
        parent::__construct($path);
        // if (! $this->isFile()) {
        //     throw new FileException("The file '{$path}' does not exist or is not a file.");
        // }
    }

    public function getContent() : string
    {
        $content = file_get_contents($this->getPathname());
        if ($content === false) {
            throw new FileException("Could not get the content of the file : '{$this->getPathname()}'");
        }
        return $content;
    }

    public function getTargetedFile(string $directory, string|null $name) : self
    {
        FileSystemUtils::createDir($directory);
        if (! is_writable($directory)) {
            throw new FileException("Unable to write into directory {$directory}.");
        }
        $fileName = StringUtils::isBlanc($name) ? $this->getBasename() : $name;
        $targetPath = $directory . DS . $fileName;
        return new self($targetPath);
    }
}