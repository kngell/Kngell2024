<?php

declare(strict_types=1);

class ViewEnvironment
{
    private const string VIEW_DIRECTORY = VIEW;
    private string $appPath;
    private Assets $assets;

    public function __construct(string $appPath, Assets $assets)
    {
        $this->assets = $assets;
        $this->appPath = $appPath;
    }

    public function getLayoutPath() : string
    {
        return self::VIEW_DIRECTORY . 'Layout';
    }

    public function getFile(string $fileName) : string|bool
    {
        $directory = self::VIEW_DIRECTORY;
        if (! str_contains($fileName, $this->appPath)) {
            $directory = $directory . $this->appPath;
        }
        return FileManager::get($directory, $fileName);
    }

    public function getCss(string|null $path = null) : string
    {
        return $this->assets->getCss($path);
    }

    public function getJs(string|null $path) : string
    {
        return $this->assets->getJs($path);
    }

    /**
     * @return string
     */
    public function getAppPath(): string
    {
        return $this->appPath;
    }
}