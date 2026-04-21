<?php

declare(strict_types=1);

class PasswordWhitespaceValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'The %s should not contain any white space';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue)
    {
    }

    public function validate(): string|bool
    {
        if (preg_match("/\s/", $this->inputValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }
        return false;
    }
}