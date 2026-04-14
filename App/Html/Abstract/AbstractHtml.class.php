<?php

declare(strict_types=1);

abstract class AbstractHtml implements HtmlComponentsInterface
{
    public const string ICON_SPRITE = 'icons-sprite.svg';

    public function getFieldSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): ?AbstractHtmlComponent
    {
        return null;
    }

    abstract public function buildLayout(HtmlBuilder $html): array;

    public function getHtmlElements(): array
    {
        return [];
    }

    abstract protected function getProviderKey(): string;

    protected function media(string|null $media): string
    {
        if (null !== $media) {
            if (StringUtils::isSerialized($media)) {
                $mediaArray = unserialize($media);
                $file = $mediaArray[0];
                $file = preg_replace('/([^:])(\/{2,})/', '$1/', $file);
                return HOST . $file;
            } else {
                return !str_contains($media, 'http') ? HOST . DS . $media : $media;
            }
        }

        return HOST . DS . 'public' . DS . 'Upload' . DS . 'images' . DS . 'default.png';
    }
}