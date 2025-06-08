<?php

declare(strict_types=1);

abstract class AbstractHtml implements HtmlComponentsInterface
{
    protected const array DESC_LIST = ['list-group'];

    protected function media(string|null $media) : string
    {
        if (null !== $media) {
            if (StringUtils::is_serialized($media)) {
                $mediaArray = unserialize($media);
                $file = $mediaArray[0];
                $file = preg_replace('/([^:])(\/{2,})/', '$1/', $file);
                return HOST . $file;
            } else {
                return ! str_contains($media, 'http') ? HOST . DS . $media : $media;
            }
        }

        return HOST . DS . 'public' . DS . 'Upload' . DS . 'images' . DS . 'default.png';
    }
}