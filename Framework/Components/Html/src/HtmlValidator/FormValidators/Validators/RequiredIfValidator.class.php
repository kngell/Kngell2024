<?php

declare(strict_types=1);

class RequiredIfValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly string $ruleValue,
        private readonly array $inputFields,
    ) {
    }

    public function validate(): array|string|bool
    {
        [$otherField, $expectedValue] = $this->parseRuleValue($this->ruleValue);

        // Get the actual value of the other field (with proper null handling)
        $otherValue = $this->getFieldValue($otherField);

        // Handle different expected value types
        if ($expectedValue !== null) {
            // Convert both values to string for comparison (like JavaScript does)
            $otherValueString = $this->convertToString($otherValue);
            $expectedValueString = $this->convertToString($expectedValue);

            if ($otherValueString === $expectedValueString && $this->isEmpty($this->inputValue)) {
                return $this->errorMessage(
                    sprintf($this->errorParams['message'], $this->display, $otherField),
                    $this->errorParams['classes'],
                );
            }
        } else {
            // No expected value - just check if other field has a value
            if (!$this->isEmpty($otherValue) && $this->isEmpty($this->inputValue)) {
                return $this->errorMessage(
                    sprintf($this->errorParams['message'], $this->display, $otherField),
                    $this->errorParams['classes'],
                );
            }
        }

        return false;
    }

    private function getFieldValue(string $fieldName): mixed
    {
        return $this->inputFields[$fieldName] ?? null;
    }

    private function parseRuleValue(string $ruleValue): array
    {
        if (str_contains($ruleValue, '=')) {
            [$field, $value] = explode('=', $ruleValue, 2);
            return [trim($field), trim($value)];
        }
        return [trim($ruleValue), null];
    }

    private function convertToString(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if ($value === null) {
            return '';
        }
        return (string) $value;
    }
}