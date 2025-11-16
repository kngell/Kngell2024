<?php

declare(strict_types=1);

class ViewEnvironment
{
    private string $appPath;
    private Assets $assets;

    public function __construct(string $appPath, Assets $assets, private FileSearchInterface $file)
    {
        $this->assets = $assets;
        $this->appPath = $appPath;
    }

    public function getLayoutPath(): string
    {
        return VIEW . 'Layout';
    }

    public function getFile(string $fileName): string
    {
        $directory = VIEW;
        if (!str_contains($fileName, $this->appPath)) {
            $directory = $directory . $this->appPath;
        }

        try {
            $fileInfo = $this->file->findViewFile($directory, $fileName);
            return $fileInfo->getPathname();
        } catch (ViewNotFoundException $e) {
            throw new ViewException("View not found: {$fileName} in directory: {$directory}");
        } catch (AmbiguousViewException $e) {
            throw new ViewException("Multiple views match: {$fileName}. " . $e->getMessage());
        }
    }

    public function getCss(string|null $path = null): string
    {
        return $this->assets->getCss($path);
    }

    public function getJs(string|null $path, string $flag): string
    {
        return $this->assets->getJs($path, $flag);
    }

    /**
     * @return string
     */
    public function getAppPath(): string
    {
        return $this->appPath;
    }
}