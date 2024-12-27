<?php

declare(strict_types=1);

class PasswordType extends AbstractInput
{
    private const string TYPE = 'password';

    public function makeForm(): string
    {
        return $this->getFormElementAttributes(self::TYPE);
    }
}