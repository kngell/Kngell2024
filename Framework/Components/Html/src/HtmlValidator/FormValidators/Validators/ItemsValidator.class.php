<?php

declare(strict_types=1);

class ItemsValidator extends AbstractValidator
{
    private string $parentFieldPath;

    public function __construct(
        private readonly array $errorParams,
        private readonly string $display, // This is the main field name like "variations"
        private readonly mixed $inputValue,
        private readonly mixed $ruleValue,
        private ?AbstractValidatorCreator $validatorCreator = null,
    ) {
        $this->parentFieldPath = $display; // Use the display name as the initial parent path
    }

    public function setValidatorCreator(AbstractValidatorCreator $validatorCreator): void
    {
        $this->validatorCreator = $validatorCreator;
    }

    public function setParentFieldPath(string $parentFieldPath): void
    {
        $this->parentFieldPath = $parentFieldPath;
    }

    public function validate(): array|string|bool
    {
        if (!is_array($this->inputValue)) {
            return false;
        }

        $itemsRule = $this->ruleValue;
        if (!is_array($itemsRule) || ($itemsRule['type'] ?? null) !== 'object' || !isset($itemsRule['rules'])) {
            return false;
        }

        $errors = [];

        foreach ($this->inputValue as $index => $item) {
            $itemErrors = $this->validateItem($item, $index, $itemsRule['rules']);
            if (!empty($itemErrors)) {
                $errors = array_merge($errors, $itemErrors);
            }
        }

        return empty($errors) ? false : $errors;
    }

    private function validateItem(array $item, int $index, array $itemRules): array
    {
        $errors = [];

        foreach ($itemRules as $field => $fieldRules) {
            $fieldValue = $item[$field] ?? null;
            $display = $fieldRules['display'] ?? $this->formatDisplayName($field);

            // Build the full field path for error reporting
            $currentFieldPath = strtolower("{$this->parentFieldPath}[{$index}][{$field}]");

            // Handle nested arrays recursively
            if (isset($fieldRules['array']) && $fieldRules['array'] === true && isset($fieldRules['items'])) {
                $nestedErrors = $this->validateNestedArray($fieldValue, $fieldRules['items'], $display, $currentFieldPath);
                if (!empty($nestedErrors)) {
                    $errors = array_merge($errors, $nestedErrors);
                }
            } else {
                $fieldError = $this->validateFieldRules($fieldValue, $fieldRules, $display, $currentFieldPath);
                if ($fieldError !== false && $fieldError !== null) {
                    $errors[$currentFieldPath] = [$fieldError];
                }
            }
        }

        return $errors;
    }

    private function validateNestedArray(mixed $value, array $itemsRule, string $display, string $currentFieldPath): array
    {
        if (!is_array($value)) {
            return [
                $currentFieldPath => [
                    $this->errorMessage(
                        sprintf($this->errorParams['message'] ?? '%s must be an array', $display),
                        $this->errorParams['classes'] ?? [],
                    ),
                ],
            ];
        }

        $nestedValidator = new ItemsValidator(
            $this->errorParams,
            $display,
            $value,
            $itemsRule,
            $this->validatorCreator,
        );
        $nestedValidator->setParentFieldPath($currentFieldPath);

        $nestedErrors = $nestedValidator->validate();
        return is_array($nestedErrors) ? $nestedErrors : [];
    }

    private function validateFieldRules(mixed $value, array $rules, string $display, string $fieldPath): string|bool
    {
        if ($this->validatorCreator === null) {
            return false;
        }

        foreach ($rules as $ruleName => $ruleValue) {
            if (in_array($ruleName, ['display', 'array', 'items'], true)) {
                continue;
            }

            // Use the validator creator to create and run the validator
            try {
                $validator = $this->validatorCreator->create($ruleName, $display, $value, $ruleValue);
                if ($validator !== null) {
                    $error = $validator->validate();
                    if ($error !== false && $error !== null) {
                        return $error;
                    }
                }
            } catch (InvalidArgumentException $e) {
                // Skip unknown rules, just like the main validator does
                continue;
            }
        }

        return false;
    }

    private function formatDisplayName(string $fieldName): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $fieldName));
    }
}