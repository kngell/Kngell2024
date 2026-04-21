<?php

declare(strict_types=1);

class PatternValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly string $ruleValue, // regex pattern
    ) {
    }

    public function validate(): array|string|bool
    {
        // Skip validation if input is empty and not required
        if ($this->inputValue === null || $this->inputValue === '') {
            return false;
        }

        if (!preg_match('/' . $this->ruleValue . '/u', (string) $this->inputValue)) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display),
                $this->errorParams['classes'],
            );
        }

        return false;
    }
}