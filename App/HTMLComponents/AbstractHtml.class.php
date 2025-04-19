<?php

declare(strict_types=1);
abstract class AbstractHtml implements HtmlComponentsInterface
{
    protected const array DESC_LIST = ['list-group'];

    protected function media(string|null $media) : string
    {
        if (null !== $media) {
            $mediaArray = unserialize($media);
            if (! empty($mediaArray)) {
                $file = $mediaArray[0];
                $file = preg_replace('/([^:])(\/{2,})/', '$1/', $file);
                return str_replace(ROOT_DIR, '', $file);
            }
        }

        return '';
    }
}