<?php

declare(strict_types=1);

class DateAfterValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly string $ruleValue,
        private readonly array $formData,
        private readonly string $fieldName,
    ) {
    }

    public function validate(): array|string|bool
    {
        if ($this->isEmpty($this->inputValue)) {
            return false;
        }

        $otherDateValue = $this->resolveFieldValue($this->ruleValue, $this->formData, $this->fieldName);
        if ($this->isEmpty($otherDateValue)) {
            return false;
        }

        $currentDate = strtotime($this->inputValue);
        $otherDate = strtotime($otherDateValue);

        if ($currentDate <= $otherDate) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display, $this->ruleValue),
                $this->errorParams['classes'],
            );
        }

        return false;
    }
}
