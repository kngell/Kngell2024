<?php

declare(strict_types=1);

class NumberType extends AbstractInput
{
    private const string TYPE = 'number';

    public function generate(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}