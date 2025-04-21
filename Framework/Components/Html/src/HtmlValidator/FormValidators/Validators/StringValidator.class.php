<?php

declare(strict_types=1);

class StringValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'The %s must be an alpha numeric field :';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue)
    {
    }

    public function validate(): string|bool
    {
        if (! preg_match('/^[A-Za-z0-9_-]*$/', $this->inputValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }
        return true;
    }
}
