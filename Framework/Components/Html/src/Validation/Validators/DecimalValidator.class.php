<?php

declare(strict_types=1);

class DecimalValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly mixed $ruleValue, // Format: ['total' => 15, 'decimals' => 5]
    ) {
    }

    public function validate(): array|string|bool
    {
        // Skip if empty (let RequiredValidator handle)
        if ($this->isEmpty($this->inputValue)) {
            return false;
        }

        // Ensure it's numeric first
        if (!is_numeric($this->inputValue)) {
            return $this->errorMessage(
                sprintf($this->errorParams['message'], $this->display),
                $this->errorParams['classes'],
            );
        }

        // Parse rule value
        $totalDigits = $this->ruleValue['total'] ?? 15;
        $decimalPlaces = $this->ruleValue['decimals'] ?? 5;
        $integerDigits = $totalDigits - $decimalPlaces;

        $valueStr = (string) $this->inputValue;
        $parts = explode('.', $valueStr);

        $integerPart = $parts[0];
        $decimalPart = $parts[1] ?? '';

        // Remove negative sign for length check
        $isNegative = str_starts_with($integerPart, '-');
        if ($isNegative) {
            $integerPart = substr($integerPart, 1);
        }

        $integerLength = strlen($integerPart);
        $decimalLength = strlen($decimalPart);

        // Check integer part length
        if ($integerLength > $integerDigits) {
            $maxValue = $this->calculateMaxValue($integerDigits, $decimalPlaces);
            return $this->errorMessage(
                sprintf(
                    $this->errorParams['messages']['max_integer'] ??
                    '%s has %d digits before decimal, maximum is %d. Maximum value: %s',
                    $this->display,
                    $integerLength,
                    $integerDigits,
                    $maxValue,
                ),
                $this->errorParams['classes'],
            );
        }

        // Check decimal part length
        if ($decimalLength > $decimalPlaces) {
            return $this->errorMessage(
                sprintf(
                    $this->errorParams['messages']['max_decimal'] ??
                    '%s has %d decimal places, maximum is %d',
                    $this->display,
                    $decimalLength,
                    $decimalPlaces,
                ),
                $this->errorParams['classes'],
            );
        }

        return false;
    }

    private function calculateMaxValue(int $integerDigits, int $decimalPlaces): string
    {
        $maxInteger = str_repeat('9', $integerDigits);
        $maxDecimal = str_repeat('9', $decimalPlaces);

        if ($decimalPlaces > 0) {
            return $maxInteger . '.' . $maxDecimal;
        }

        return $maxInteger;
    }
}