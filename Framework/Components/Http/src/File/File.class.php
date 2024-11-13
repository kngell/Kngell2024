<?php

declare(strict_types=1);
class File extends SplFileInfo
{
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
        $targetPath = $directory . DIRECTORY_SEPARATOR . $fileName;
        return new self($targetPath);
    }
}
