<?php

declare(strict_types=1);

class PasswordLetterValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'The %s needs at least one letter';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue)
    {
    }

    public function validate(): string|bool
    {
        if (preg_match('/.*[a-z]+.*/i', $this->inputValue) == 0) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
            return true;
        }
        return true;
    }
}