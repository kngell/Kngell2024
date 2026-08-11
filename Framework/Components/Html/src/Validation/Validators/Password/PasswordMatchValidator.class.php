<?php

declare(strict_types=1);

class PasswordMatchValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'The %s does not match the password';

    public function __construct(private string $display, private mixed $inputValue, private mixed $ruleValue, private ?Model $model, private array $inputFields)
    {
    }

    public function validate(): string|bool
    {
        if ($this->password() !== $this->inputValue) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }
        return false;
    }

    private function password() : string
    {
        if (! array_key_exists('password', $this->inputFields)) {
            throw new ValidatorUndefinedValueException('The password is missing');
        }
        return $this->inputFields['password'];
    }
}