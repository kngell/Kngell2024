<?php

declare(strict_types=1);
class PasswordSmallLetterValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'The %s should contain at least one small Letter';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue)
    {
    }

    public function validate(): string|bool
    {
        if (! preg_match('/[a-z]/', $this->inputValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }
        return false;
    }
}