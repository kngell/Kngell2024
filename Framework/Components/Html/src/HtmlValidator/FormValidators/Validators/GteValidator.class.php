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
        $comparisonValue = $this->allData[$this->ruleValue] ?? null;

        if (!$this->isEmpty($this->inputValue) && !$this->isEmpty($comparisonValue)) {
            if ($this->inputValue < $comparisonValue) {
                return $this->errorMessage(
                    sprintf($this->errorParams['message'], $this->display, $comparisonValue),
                    $this->errorParams['classes'],
                );
            }
        }
        return false;
    }
}