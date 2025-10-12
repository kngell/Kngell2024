<?php

declare(strict_types=1);

final class Validator implements ValidatorInterface
{
    // Configuration defaults
    private const bool DEFAULT_SANITIZE = true;
    private const bool DEFAULT_STOP_ON_ERROR = false;
    private const bool DEFAULT_VALIDATE_ALL_FIELDS = false;
    private const bool DEFAULT_SKIP_MISSING_FIELDS = true;

    private array $inputRules = [];
    private ?AbstractValidatorCreator $validatorCreator = null;
    private array $inputFields = [];
    private array $validatedData = [];
    private ?ValidationResult $lastResult = null;

    public function __construct(
        private readonly ValidationConfig $config,
    ) {
    }

    public function validate(array $inputFields, string $rules, ?Model $model = null): ValidationResult
    {
        try {
            $this->initializeValidation($inputFields, $rules, $model);
            $this->lastResult = $this->performValidation();
            return $this->lastResult;
        } catch (ValidationException $e) {
            // Re-throw ValidationException to preserve specific error codes
            throw $e;
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
     * Get validated data from last validation run.
     */
    public function getValidatedData(): array
    {
        return $this->validatedData;
    }

    /**
     * Check if last validation had errors.
     */
    public function hasErrors(): bool
    {
        return $this->lastResult !== null && !$this->lastResult->isValid();
    }

    /**
     * Add custom validation rule to the current validator creator.
     */
    public function addCustomRule(string $ruleName, callable $validator): void
    {
        if ($this->validatorCreator === null) {
            throw new LogicException('Validator creator not initialized. Call validate() first.');
        }

        if (!method_exists($this->validatorCreator, 'addCustomRule')) {
            throw new LogicException('Current validator creator does not support custom rules');
        }

        $this->validatorCreator->addCustomRule($ruleName, $validator);
    }

    public function validateFieldOnly(string $fieldName, mixed $value, array $fieldRules): array
    {
        $display = $fieldRules['display'] ?? $this->generateDisplayName($fieldName);
        $errors = [];

        foreach ($fieldRules as $ruleName => $ruleValue) {
            if (in_array($ruleName, ['display', 'when'], true)) {
                continue;
            }

            $error = $this->executeValidationRule($ruleName, $display, $value, $ruleValue, $fieldName);

            if (is_string($error) && $error !== '') {
                $errors[] = $error;
            }
        }

        return $errors;
    }

    private function initializeValidation(array $inputFields, string $ruleFileName, ?Model $model): void
    {
        $rulesFile = FileManager::get(APP . 'Forms', $ruleFileName . '.yaml');

        if (!$rulesFile || !file_exists($rulesFile)) {
            throw ValidationException::rulesFileNotFound($ruleFileName);
        }

        $this->inputRules = YamlFile::get($rulesFile);

        if (!is_array($this->inputRules)) {
            throw ValidationException::invalidRulesFormat($ruleFileName);
        }

        $this->validatorCreator = ValidatorCreatorFactory::create(
            $ruleFileName,
            $model,
            $inputFields,
            $this->config,
        );
        $this->inputFields = $this->config->shouldSanitizeInput()
            ? $this->sanitizeInputFields($inputFields)
            : $inputFields;
        $this->validatedData = [];
        $this->lastResult = null;
    }

    private function performValidation(): ValidationResult
    {
        $this->beforeValidation();

        $errors = [];
        $fieldsToValidate = $this->getFieldsToValidate();

        foreach ($fieldsToValidate as $fieldName => $fieldRules) {
            if (!$this->shouldValidateField($fieldName, $fieldRules)) {
                continue;
            }

            $fieldErrors = $this->validateField($fieldName, $fieldRules);

            if (!empty($fieldErrors)) {
                // Handle different error formats from different validators
                foreach ($fieldErrors as $errorKey => $errorValue) {
                    if (is_numeric($errorKey)) {
                        // Regular field error - store under fieldName
                        $errors[$fieldName][] = $errorValue;
                    } else {
                        // Nested error from ItemsValidator - store with full path
                        $errors[$errorKey] = $errorValue;
                    }
                }

                if ($this->config->shouldStopOnFirstError()) {
                    break;
                }
            } else {
                // Store validated data for successful fields
                $this->storeValidatedData($fieldName, $this->inputFields[$fieldName] ?? null);
            }
        }

        $result = new ValidationResult($errors, $this->validatedData, $this->config->shouldStopOnFirstError());

        return $this->afterValidation($result);
    }

    private function storeValidatedData(string $fieldName, mixed $value): void
    {
        // Handle nested field storage
        if (str_contains($fieldName, '[')) {
            $this->storeNestedValidatedData($fieldName, $value);
        } else {
            $this->validatedData[$fieldName] = $value;
        }
    }

    private function storeNestedValidatedData(string $fieldName, mixed $value): void
    {
        $parts = preg_split('/[\[\]]+/', $fieldName, -1, PREG_SPLIT_NO_EMPTY);
        $current = &$this->validatedData;

        foreach ($parts as $part) {
            if (!isset($current[$part]) || !is_array($current[$part])) {
                $current[$part] = [];
            }
            $current = &$current[$part];
        }

        $current = $value;
    }

    private function getFieldsToValidate(): array
    {
        $shouldValidateAll = $this->config->shouldValidateAllFields();
        $shouldSkipMissing = $this->config->shouldSkipMissingFields();

        if (!$shouldValidateAll || $shouldSkipMissing) {
            return array_intersect_key($this->inputRules, $this->inputFields);
        }

        return $this->inputRules;
    }

    private function shouldValidateField(string $fieldName, array $fieldRules): bool
    {
        // Check if field has conditional validation using 'required_if' or similar
        if (isset($fieldRules['when'])) {
            return $this->evaluateCondition($fieldRules['when']);
        }

        return true;
    }

    private function validateField(string $fieldName, array $fieldRules): array
    {
        $errors = [];
        $display = $fieldRules['display'] ?? $this->generateDisplayName($fieldName);
        $inputValue = $this->inputFields[$fieldName] ?? null;

        // Remove display and when from rules to avoid processing them as validation rules
        $validationRules = $fieldRules;
        unset($validationRules['display'], $validationRules['when']);

        // Check validation groups
        if ($this->config->hasValidationGroups()) {
            $validationRules = $this->filterRulesByGroups($validationRules);
        }

        foreach ($validationRules as $ruleName => $ruleValue) {
            $error = $this->executeValidationRule($ruleName, $display, $inputValue, $ruleValue, $fieldName);

            if ($error !== false && $error !== null && $error !== '') {
                // Handle different return types from validators
                if (is_array($error)) {
                    // ItemsValidator returns associative array with full field paths
                    $errors = array_merge($errors, $error);
                } else {
                    // Regular validator returns string error
                    $errors[] = $error;
                }

                if ($this->config->shouldStopOnFirstError()) {
                    break;
                }
            }
        }

        return $errors;
    }

    private function executeValidationRule(string $ruleName, string $display, mixed $inputValue, mixed $ruleValue, string $fieldName): array|string|bool|null
    {
        if ($this->validatorCreator === null) {
            throw ValidationException::validatorCreatorNotInitialized();
        }

        try {
            return $this->validatorCreator->run($ruleName, $display, $inputValue, $ruleValue);
        } catch (InvalidArgumentException $e) {
            // Unknown rule - log and skip
            error_log("Unknown validation rule '{$ruleName}' for field '{$fieldName}': " . $e->getMessage());
            return null;
        } catch (ValidationException $e) {
            // Re-throw ValidationException to preserve specific error codes
            throw $e;
        } catch (Throwable $th) {
            // System errors - log and return generic message
            error_log("Validation rule '{$ruleName}' failed for field '{$fieldName}': " . $th->getMessage());
            return $this->formatSystemError($display);
        }
    }

    private function filterRulesByGroups(array $rules): array
    {
        $groups = $this->config->getValidationGroups();

        if (empty($groups)) {
            return $rules;
        }

        return array_filter($rules, function ($ruleValue, $ruleName) use ($groups) {
            // Support both simple rule names and rule names with groups
            $ruleGroups = $this->extractRuleGroups($ruleName);
            return empty($ruleGroups) || !empty(array_intersect($groups, $ruleGroups));
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function extractRuleGroups(string $ruleName): array
    {
        if (str_contains($ruleName, '|')) {
            [$rule, $groups] = explode('|', $ruleName, 2);
            return array_map('trim', explode(',', $groups));
        }

        return [];
    }

    private function sanitizeInputFields(array $inputFields): array
    {
        return array_map(function ($value) {
            if (is_string($value)) {
                $value = trim($value);
                // Only sanitize if it's not already HTML
                if ($this->config->shouldSanitizeHtml()) {
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                }
            } elseif (is_array($value)) {
                return $this->sanitizeInputFields($value);
            }
            return $value;
        }, $inputFields);
    }

    /**
     * Generate display name from field name.
     */
    private function generateDisplayName(string $fieldName): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $fieldName));
    }

    /**
     * Evaluate conditional validation.
     */
    private function evaluateCondition(mixed $condition): bool
    {
        // Simple implementation for your use case
        if (is_bool($condition)) {
            return $condition;
        }

        if (is_string($condition)) {
            // Handle cases like 'required_if: status'
            return !empty($this->inputFields[$condition] ?? null);
        }

        if (is_callable($condition)) {
            return $condition($this->inputFields);
        }

        return true;
    }

    /**
     * Format validation error message.
     */
    private function formatValidationError(string $display, string $message): string
    {
        $class = $this->config->getHintClasses();
        return "<div class='" . implode(' ', $class) . "'>" .
               nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8')) . '</div>';
    }

    /**
     * Format system error message.
     */
    private function formatSystemError(string $display): string
    {
        return $this->formatValidationError($display, "Validation error occurred for {$display}");
    }

    /**
     * Hook method called before validation starts.
     */
    private function beforeValidation(): void
    {
        // Can be overridden for custom pre-validation logic
        // Example: Add custom rules based on input fields
    }

    /**
     * Hook method called after validation completes.
     */
    private function afterValidation(ValidationResult $result): ValidationResult
    {
        // Can be overridden for custom post-validation logic
        // Example: Cross-field validation that couldn't be done in individual validators
        return $result;
    }

    /**
     * Factory method for common validation scenarios.
     */
    public static function create(?ValidationConfig $config = null): self
    {
        return new self($config ?? self::createDefaultConfig());
    }

    /**
     * Factory method for quick validation that stops on first error.
     */
    public static function quick(): self
    {
        return new self(new ValidationConfig(
            sanitizeInput: self::DEFAULT_SANITIZE,
            stopOnFirstError: true, // Override default
            validateAllFields: self::DEFAULT_VALIDATE_ALL_FIELDS,
            skipMissingFields: self::DEFAULT_SKIP_MISSING_FIELDS,
        ));
    }

    /**
     * Factory method for validation with specific groups.
     */
    public static function withGroups(array $groups): self
    {
        return new self(new ValidationConfig(
            sanitizeInput: self::DEFAULT_SANITIZE,
            stopOnFirstError: self::DEFAULT_STOP_ON_ERROR,
            validateAllFields: self::DEFAULT_VALIDATE_ALL_FIELDS,
            skipMissingFields: self::DEFAULT_SKIP_MISSING_FIELDS,
            validationGroups: $groups,
        ));
    }

    /**
     * Factory method for strict validation (validate all fields).
     */
    public static function strict(): self
    {
        return new self(new ValidationConfig(
            sanitizeInput: self::DEFAULT_SANITIZE,
            stopOnFirstError: self::DEFAULT_STOP_ON_ERROR,
            validateAllFields: true, // Override default
            skipMissingFields: false, // Override default
        ));
    }

    /**
     * Add custom rule globally for a specific rule set.
     */
    public static function addGlobalCustomRule(string $ruleSet, string $ruleName, callable $validator): void
    {
        // You could store these in a static property or configuration
        // This would require modifying ValidatorCreatorFactory to inject global custom rules
    }

    /**
     * Create default configuration using constants.
     */
    private static function createDefaultConfig(): ValidationConfig
    {
        return new ValidationConfig(
            sanitizeInput: self::DEFAULT_SANITIZE,
            stopOnFirstError: self::DEFAULT_STOP_ON_ERROR,
            validateAllFields: self::DEFAULT_VALIDATE_ALL_FIELDS,
            skipMissingFields: self::DEFAULT_SKIP_MISSING_FIELDS,
        );
    }
}