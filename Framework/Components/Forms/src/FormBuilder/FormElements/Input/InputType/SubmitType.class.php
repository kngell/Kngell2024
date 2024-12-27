<?php

declare(strict_types=1);

class SubmitType extends AbstractInput
{
    private const string TYPE = 'submit';

    public function makeForm(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}