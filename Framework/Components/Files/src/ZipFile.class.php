<?php

declare(strict_types=1);

class ZipFile
{
    private ZipArchive $zipArchive;

    public function __construct(private string $zipFile)
    {
        $this->zipArchive = new ZipArchive();
    }

    public function open() : bool
    {
        if ($this->zipArchive->open($this->zipFile) === true) {
            return true;
        } else {
            throw new FileException('Failed to open zip file: ' . $this->zipFile);
        }
    }

    public function addFile(string $filePath, string $localName) : bool
    {
        if ($this->zipArchive->addFile($filePath, $localName) === true) {
            return true;
        } else {
            throw new FileException('Failed to add file to zip: ' . $filePath);
        }
    }

    public function extractTo(string $destination) : bool
    {
        if ($this->zipArchive->extractTo($destination) === true) {
            return true;
        } else {
            throw new FileException('Failed to extract zip file: ' . $this->zipFile);
        }
    }

    public function deleteFile(string $name) : bool
    {
        if ($this->zipArchive->deleteName($name) === true) {
            return true;
        } else {
            throw new FileException('Failed to delete file from zip: ' . $name);
        }
    }

    public function getFileComment(string $name) : string
    {
        return $this->zipArchive->getCommentName($name);
    }

    public function setFileComment(string $name, string $comment) : bool
    {
        if ($this->zipArchive->setCommentName($name, $comment) === true) {
            return true;
        } else {
            throw new FileException('Failed to set file comment in zip: ' . $name);
        }
    }

    public function count() : int
    {
        return $this->zipArchive->count();
    }

    public function getFileNames() : array
    {
        $fileNames = [];
        for ($i = 0; $i < $this->count(); $i++) {
            $fileNames[] = $this->zipArchive->getNameIndex($i);
        }
        return $fileNames;
    }

    public function getFromName(string $name) : string
    {
        return $this->zipArchive->getFromName($name);
    }

    public function close() : void
    {
        $this->zipArchive->close();
    }
}