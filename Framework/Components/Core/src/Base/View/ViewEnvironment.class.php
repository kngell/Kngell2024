<?php

declare(strict_types=1);

class ViewEnvironment
{
    private const string VIEW_DIRECTORY = '/home/kngell/projects/kngell-ecom/App/Views';
    private string $layout;
    private string $appPath;

    private Assets $assets;

    public function __construct(string $appPath, Assets $assets)
    {
        $this->layout = dirname(getcwd()) . '/App/Views/Frontend/layout';
        $this->appPath = $appPath;
        $this->assets = $assets;
    }

    public function getLayout(string $layout) : string
    {
        return $this->layout . DS . $layout . '.php';
    }

    public function getFile(string $fileName) : string|bool
    {
        $directory = self::VIEW_DIRECTORY;
        if (! str_contains($fileName, $this->appPath)) {
            $directory = $directory . DS . $this->appPath;
        }
        return FileManager::get($directory, $fileName);
    }

    public function getCss(string $path) : string
    {
        return $this->assets->getCss($path);
    }

    public function getJs(string|null $path) : string
    {
        return $this->assets->getJs($path);
    }
}
