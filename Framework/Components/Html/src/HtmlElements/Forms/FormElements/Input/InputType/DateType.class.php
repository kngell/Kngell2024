<?php

declare(strict_types=1);

class DateType extends AbstractInput
{
    private const string TYPE = 'date';

    public function generate(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}