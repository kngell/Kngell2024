<?php

declare(strict_types=1);

class NumericValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = '%s must be a numeric value';

    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly mixed $ruleValue,
    ) {
    }

    public function validate(): array|string|bool
    {
        if ($this->inputValue === null || $this->inputValue === '') {
            return false; // skip if empty, let RequiredValidator handle that
        }

        if (!is_numeric($this->inputValue)) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display),
                $this->errorParams['classes'],
            );
        }

        return false;
    }
}