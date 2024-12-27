<?php

declare(strict_types=1);

class CheckBoxType extends AbstractInput
{
    private const string TYPE = 'checkbox';

    public function makeForm(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}