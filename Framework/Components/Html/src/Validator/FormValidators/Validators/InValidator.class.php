<?php

declare(strict_types=1);

class InValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly array $ruleValue,
    ) {
    }

    public function validate(): array|string|bool
    {
        if (!$this->isEmpty($this->inputValue) && !in_array($this->inputValue, $this->ruleValue, true)) {
            $allowedValues = implode(', ', $this->ruleValue);
            return $this->errorMessage(
                sprintf('%s must be one of: %s', $this->display, $allowedValues),
                $this->errorParams['classes'],
            );
        }

        return false;
    }
}