<?php

declare(strict_types=1);

class RadioType extends AbstractInput
{
    private const string TYPE = 'radio';

    public function makeForm(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}