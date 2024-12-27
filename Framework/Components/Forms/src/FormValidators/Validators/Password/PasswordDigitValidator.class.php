<?php

declare(strict_types=1);

class PasswordDigitValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'The %s should contain at least one digit';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue)
    {
    }

    public function validate(): string|bool
    {
        if (! preg_match("/\d/", $this->inputValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }
        return true;
    }
}