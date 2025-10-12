<?php

declare(strict_types=1);
class MaxValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = '%s must be a maximum of %s characters';

    public function __construct(
        private readonly array $errorParams,
        private string $display,
        private mixed $inputValue,
        private mixed $ruleValue,
    ) {
    }

    public function validate(): array|string|bool
    {
        // Skip validation if input is empty and not required
        if ($this->inputValue === null || $this->inputValue === '') {
            return false;
        }

        if (!(strlen($this->inputValue) <= $this->ruleValue)) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display),
                $this->errorParams['classes'],
            );
        }
        return false;
    }
}