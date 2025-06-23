<?php

declare(strict_types=1);

final class Validator implements ValidatorInterface
{
    private array $inputRules = [];
    private ?AbstractValidatorCreator $validatorCreator = null;
    private array $inputFields = [];
    private array $validatedData = [];

    public function __construct(
        private readonly ValidationConfig $config = new ValidationConfig()
    ) {
    }

    public function validate(array $inputFields, string $rules, ?Model $model = null): ValidationResult
    {
        try {
            $this->initializeValidation($inputFields, $rules, $model);
            return $this->performValidation();
        } catch (Throwable $th) {
            throw ValidationException::rulesFileNotFound($rules);
        }
    }

    /**
     * Legacy method for backward compatibility.
     */
    public function validateLegacy(array $inputFields, string $rules, ?Model $model = null): array|bool
    {
        $result = $this->validate($inputFields, $rules, $model);
        return $result->isValid() ? true : $result->getErrors();
    }

    /**
     * Factory method for common validation scenarios.
     */
    public static function create(?ValidationConfig $config = null): self
    {
        return new self($config ?? ValidationConfig::default());
    }

    /**
     * Factory method for quick validation that stops on first error.
     */
    public static function quick(): self
    {
        return new self(ValidationConfig::stopOnFirstError());
    }

    /**
     * Factory method for validation with specific groups.
     */
    public static function withGroups(array $groups): self
    {
        return new self(ValidationConfig::withGroups($groups));
    }

    private function initializeValidation(array $inputFields, string $ruleFileName, ?Model $model): void
    {
        $rulesFile = FileManager::get(APP . 'Forms', $ruleFileName . '.yaml');

        if (! $rulesFile || ! file_exists($rulesFile)) {
            throw ValidationException::rulesFileNotFound($ruleFileName);
        }

        $this->inputRules = YamlFile::get($rulesFile);

        if (! is_array($this->inputRules)) {
            throw ValidationException::invalidRulesFormat($ruleFileName);
        }

        $this->validatorCreator = ValidatorCreatorFactory::create($ruleFileName, $model, $inputFields);
        $this->inputFields = $this->config->shouldSanitizeInput()
            ? $this->sanitizeInputFields($inputFields)
            : $inputFields;
        $this->validatedData = [];
    }

    private function performValidation(): ValidationResult
    {
        $errors = [];
        $fieldsToValidate = $this->getFieldsToValidate();

        foreach ($fieldsToValidate as $fieldName => $fieldRules) {
            $fieldErrors = $this->validateField($fieldName, $fieldRules);

            if (! empty($fieldErrors)) {
                $errors[$fieldName] = $fieldErrors;

                if ($this->config->shouldStopOnFirstError()) {
                    break;
                }
            } else {
                // Store validated data for successful fields
                $this->validatedData[$fieldName] = $this->inputFields[$fieldName] ?? null;
            }
        }

        return new ValidationResult($errors, $this->validatedData, $this->config->shouldStopOnFirstError());
    }

    private function getFieldsToValidate(): array
    {
        if (! $this->config->shouldValidateAllFields()) {
            // Only validate fields that are present in input
            return array_filter(
                $this->inputRules,
                fn ($fieldName) => array_key_exists($fieldName, $this->inputFields),
                ARRAY_FILTER_USE_KEY
            );
        }

        if ($this->config->shouldSkipMissingFields()) {
            return array_filter(
                $this->inputRules,
                fn ($fieldName) => array_key_exists($fieldName, $this->inputFields),
                ARRAY_FILTER_USE_KEY
            );
        }

        return $this->inputRules;
    }

    private function validateField(string $fieldName, array $fieldRules): array
    {
        $errors = [];
        $display = $fieldRules['display'] ?? ucfirst(str_replace('_', ' ', $fieldName));
        $inputValue = $this->inputFields[$fieldName] ?? null;

        // Remove display from rules to avoid processing it as a validation rule
        $validationRules = $fieldRules;
        unset($validationRules['display']);

        // Check validation groups
        if ($this->config->hasValidationGroups()) {
            $validationRules = $this->filterRulesByGroups($validationRules);
        }

        foreach ($validationRules as $ruleName => $ruleValue) {
            $error = $this->executeValidationRule($ruleName, $display, $inputValue, $ruleValue);

            if ($error !== null && $error !== false && $error !== '') {
                $errors[] = is_string($error) ? $error : "Validation failed for {$display}";

                if ($this->config->shouldStopOnFirstError()) {
                    break;
                }
            }
        }

        return $errors;
    }

    private function executeValidationRule(string $ruleName, string $display, mixed $inputValue, mixed $ruleValue): string|bool|null
    {
        if ($this->validatorCreator === null) {
            throw new ValidationException('Validator creator not initialized');
        }

        try {
            return $this->validatorCreator->run($ruleName, $display, $inputValue, $ruleValue);
        } catch (Throwable $th) {
            // Log the error but don't break validation
            error_log("Validation rule '{$ruleName}' failed: " . $th->getMessage());
            return "Validation error occurred for {$display}";
        }
    }

    private function filterRulesByGroups(array $rules): array
    {
        $groups = $this->config->getValidationGroups();

        if (empty($groups)) {
            return $rules;
        }

        // This is a simplified implementation
        // You could extend this to support more complex group filtering
        return array_filter($rules, function ($ruleName) use ($groups) {
            return in_array($ruleName, $groups, true);
        }, ARRAY_FILTER_USE_KEY);
    }

    private function sanitizeInputFields(array $inputFields): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
            }
            return $value;
        }, $inputFields);
    }
}