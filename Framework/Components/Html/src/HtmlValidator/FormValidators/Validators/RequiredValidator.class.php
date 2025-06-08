<?php

declare(strict_types=1);

class RequiredValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = '%s is required';

    public function __construct(
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly mixed $ruleValue
    ) {
    }

    public function validate(): string|bool
    {
        if ($this->isEmpty($this->inputValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }
        return false;
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null || $value === '' || $value === '[]') {
            return true;
        }

        if (is_array($value) && empty($value)) {
            return true;
        }

        if (is_string($value) && trim($value) === '') {
            return true;
        }

        return false;
    }
}