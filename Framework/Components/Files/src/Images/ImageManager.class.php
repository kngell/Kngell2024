<?php

declare(strict_types=1);

class ImageManager implements FilesManagerInterface
{
    private const int MAX_DIMENSION = 400;
    private string $originalPath;
    private string $destinationPath;
    private int $width;
    private int $height;
    private int $newWidth;
    private int $newHeight;
    private float $scaleFactor;

    public function __construct(string $originalPath, string $destinationPath)
    {
        $this->originalPath = $originalPath;
        $this->destinationPath = $destinationPath;
        [$this->width, $this->height] = getimagesize($originalPath);
        $this->scaleFactor = self::MAX_DIMENSION / max($this->width, $this->height);
    }

    public function resize() : bool
    {
        $oldImg = $this->oldImg();
        $newImg = $this->newImg();
        imagecopyresampled($newImg, $oldImg, 0, 0, 0, 0, $this->newWidth, $this->newHeight, $this->width, $this->height);
        return imagejpeg($newImg, $this->destinationPath);
    }

    private function newImg() : GdImage|false
    {
        $this->newWidth = intval($this->width * $this->scaleFactor);
        $this->newHeight = intval($this->height * $this->scaleFactor);
        return imagecreatetruecolor($this->newWidth, $this->newHeight);
    }

    private function oldImg() : GdImage|false
    {
        return imagecreatefromjpeg($this->originalPath);
    }
}