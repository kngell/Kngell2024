<?php

declare(strict_types=1);
class RequiredValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = '%s is required';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue)
    {
    }

    public function validate() : string|bool
    {
        if ((empty($this->inputValue) || $this->inputValue === '[]')) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }
        return false;
    }
}