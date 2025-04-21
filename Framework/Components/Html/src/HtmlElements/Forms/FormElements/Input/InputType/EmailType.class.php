<?php

declare(strict_types=1);

class EmailType extends AbstractInput
{
    private const string TYPE = 'email';

    public function generate(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}