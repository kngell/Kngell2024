<?php

declare(strict_types=1);

class HtmlTemplatePathManager implements HtmlTemplatePathInterface
{
    private string $templatePath = APP . 'Html' . DS . 'Templates' . DS;

    public function __construct(private FileContentInterface $file)
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
            'confirmProductDeletionModal' => $this->templatePath . 'confirmProductDeletionModal.php',
        ];
    }

    private function files(): array
    {
        return [
        ];
    }
}