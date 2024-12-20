<?php

declare(strict_types=1);
class MinValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = '%s must be a minimum of %s characters';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue)
    {
    }

    public function validate() : string|bool
    {
        if (! (strlen($this->inputValue) >= $this->ruleValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display, $this->ruleValue));
        }
        return false;
    }
}