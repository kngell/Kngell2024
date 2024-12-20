<?php

declare(strict_types=1);

class UniqueValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'This %s already exist';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue, private Model $md)
    {
    }

    public function validate(): string|bool
    {
        if (! empty($this->inputValue)) {
            $user = $this->md->all(['email', $this->inputValue]);
            if ($user->getNumRows() >= 1) {
                return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
            }
        }
        return true;
    }
}