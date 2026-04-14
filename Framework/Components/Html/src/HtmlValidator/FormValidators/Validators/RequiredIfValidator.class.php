<?php

declare(strict_types=1);

class RequiredIfValidator extends AbstractValidator
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
        [$otherField, $expectedValue] = $this->parseRuleValue($this->ruleValue);

        $otherValue = $this->resolveFieldValue($otherField, $this->formData, $this->fieldName);

        if ($expectedValue !== null) {
            $otherValueString = $this->convertToString($otherValue);
            $expectedValueString = $this->convertToString($expectedValue);

            if ($otherValueString === $expectedValueString && $this->isEmpty($this->inputValue)) {
                return $this->errorMessage(
                    sprintf($this->errorParams['message'], $this->display, $otherField),
                    $this->errorParams['classes'],
                );
            }
        } else {
            if (!$this->isEmpty($otherValue) && $this->isEmpty($this->inputValue)) {
                return $this->errorMessage(
                    sprintf($this->errorParams['message'], $this->display, $otherField),
                    $this->errorParams['classes'],
                );
            }
        }

        return false;
    }

    private function parseRuleValue(string $ruleValue): array
    {
        if (str_contains($ruleValue, '=')) {
            [$field, $value] = explode('=', $ruleValue, 2);
            return [trim($field), trim($value)];
        }
        return [trim($ruleValue), null];
    }
}
