<?php

declare(strict_types=1);

class HiddenType extends AbstractInput
{
    private const string TYPE = 'hidden';

    public function makeForm(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}