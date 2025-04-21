<?php

declare(strict_types=1);

class StriptagValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'The %s must not have html tags!';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue)
    {
    }

    public function validate(): string|bool
    {
        if ($this->inputValue != strip_tags($this->inputValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }
        return true;
    }
}
