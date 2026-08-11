<?php

declare(strict_types=1);

class SameValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly string $ruleValue, // The name of the field to compare against
        private readonly array $formData,
        private readonly string $fieldName,
    ) {
    }

    public function validate(): array|string|bool
    {
        // 1. Resolve the value of the field we are comparing against
        $otherValue = $this->resolveFieldValue($this->ruleValue, $this->formData, $this->fieldName);

        // 2. Compare. Use strict string comparison for consistency
        if ((string) $this->inputValue !== (string) $otherValue) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display, $this->ruleValue),
                $this->errorParams['classes'],
            );
        }

        return false;
    }
}
