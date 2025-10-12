<?php

declare(strict_types=1);

class GteValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly mixed $ruleValue,
    ) {
    }

    public function validate(): array|string|bool
    {
        if (!$this->isEmpty($this->inputValue) && $this->inputValue < $this->ruleValue) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display, $this->ruleValue),
                $this->errorParams['classes'],
            );
        }

        return false;
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }
}