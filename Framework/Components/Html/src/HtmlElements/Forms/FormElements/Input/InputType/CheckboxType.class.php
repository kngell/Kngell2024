<?php

declare(strict_types=1);

class CheckboxType extends AbstractInput
{
    private const string TYPE = 'checkbox';

    public function generate(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}