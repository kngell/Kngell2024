<?php

declare(strict_types=1);

class TextType extends AbstractInput
{
    private const string TYPE = 'text';

    public function generate(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}