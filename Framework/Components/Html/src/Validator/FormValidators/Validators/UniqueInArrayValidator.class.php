<?php

declare(strict_types=1);

class UniqueInArrayValidator extends AbstractValidator
{
    public function __construct(
        private readonly array $errorParams,
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly mixed $ruleValue,
        private readonly ?array $formData,
        private readonly string $fieldName, // e.g., "variations[0][sku]"
    ) {
    }

    public function validate(): array|string|bool
    {
        if ($this->isEmpty($this->inputValue) || $this->formData === null) {
            return false;
        }

        // 1. Extract the parent array key (e.g., "variations")
        preg_match('/^([^\[]+)/', $this->fieldName, $matches);
        $parentKey = $matches[1] ?? '';

        if (empty($parentKey) || !isset($this->formData[$parentKey])) {
            return false;
        }

        // 2. Identify the sub-field (e.g., "sku")
        $subField = $this->extractFieldName($this->fieldName);

        // 3. Count occurrences of this value within the sibling items
        $count = 0;
        foreach ($this->formData[$parentKey] as $item) {
            if (isset($item[$subField]) && (string) $item[$subField] === (string) $this->inputValue) {
                $count++;
            }
        }
        list($message, $classes) = $this->buildErrorMessage($this->errorParams);
        // 4. If found more than once, it's a duplicate in the form
        if ($count > 1) {
            return $this->errorMessage(
                sprintf($message, $this->display),
                $classes,
            );
        }

        return false;
    }
}
