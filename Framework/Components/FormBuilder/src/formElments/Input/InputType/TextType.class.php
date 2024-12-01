<?php

declare(strict_types=1);

class TextType extends InputElement
{
    private const string TYPE = 'text';

    public function makeForm(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}
