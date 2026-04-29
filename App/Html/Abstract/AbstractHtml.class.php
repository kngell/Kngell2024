<?php

declare(strict_types=1);

abstract class AbstractHtml
{
    public function getFieldSectionLayout(
        array $fields,
        string $sectionKey,
        HtmlBuilder $form,
    ): ?AbstractHtmlComponent {
        return null;
    }

    /** @return AbstractHtmlComponent[] */
    public function getHtmlElements(): array
    {
        return [];
    }

    public function getTable(): ?string
    {
        return null;
    }

    /** @return AbstractHtmlComponent[] */
    abstract public function buildLayout(?HtmlBuilder $html = null): array;

    protected function getProviderKey(): ?string
    {
        return null;
    }

    protected function media(?string $media): string
    {
        if ($media === null) {
            return $this->getDefaultMediaPath();
        }

        if (StringUtils::isSerialized($media)) {
            return $this->resolveSerializedMedia($media);
        }

        return $this->resolveMediaPath($media);
    }

    private function getDefaultMediaPath(): string
    {
        return HOST . DS . 'public' . DS . 'Upload' . DS . 'images' . DS . 'default.png';
    }

    private function resolveSerializedMedia(string $media): string
    {
        $mediaArray = unserialize($media, ['allowed_classes' => false]);
        $file = $mediaArray[0] ?? '';
        $file = preg_replace('/([^:])(\/{2,})/', '$1/', $file);

        return HOST . $file;
    }

    private function resolveMediaPath(string $media): string
    {
        if (str_contains($media, 'http')) {
            return $media;
        }

        return HOST . DS . $media;
    }
}