<?php

declare(strict_types=1);

class HtmlTemplatePathManager implements HtmlTemplatePathInterface
{
    private string $basePath = APP . 'Html' . DS . 'Components' . DS;

    public function __construct(private FileContentManager $file, private FileSearchManager $fileSearch)
    {
    }

    public function getTemplate(string $fileName): ?string
    {
        $path = $this->getPath($fileName);
        return $this->file->read($path);
    }

    private function getPath(string $fileName): ?string
    {
        $paths = array_merge($this->templates(), $this->files());
        if (array_key_exists($fileName, $paths)) {
            return $paths[$fileName];
        }
        return null;
    }

    private function templates(): array
    {
        return [
            'confirmDeletionModal' => $this->fileSearch->findFile($this->basePath, 'confirmDeletionModal.php')->getPathname(),
        ];
    }

    private function files(): array
    {
        return [
        ];
    }
}