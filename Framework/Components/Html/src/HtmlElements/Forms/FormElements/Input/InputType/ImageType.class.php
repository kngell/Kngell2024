<?php

declare(strict_types=1);

class ImageType extends AbstractInput
{
    private const string TYPE = 'image';

    public function generate(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}