<?php

declare(strict_types=1);

class FileType extends AbstractInput
{
    private const string TYPE = 'file';

    public function generate(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}