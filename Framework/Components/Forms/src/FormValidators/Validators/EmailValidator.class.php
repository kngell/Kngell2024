<?php

declare(strict_types=1);

class EmailValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'The %s field is not a valid email format';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue)
    {
    }

    public function validate(): string|bool
    {
        if (! empty($this->inputValue) && ! filter_var($this->inputValue, FILTER_VALIDATE_EMAIL) && ! checkdnsrr(substr($this->inputValue, strpos($this->inputValue, '@') + 1), 'MX')) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }
        return true;
    }
}