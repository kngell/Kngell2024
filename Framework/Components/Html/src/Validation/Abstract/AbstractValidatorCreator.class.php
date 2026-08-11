<?php

declare(strict_types=1);

abstract class AbstractValidatorCreator
{
    private const array SIMPLE_RULES = [];

    protected array $messages;
    protected array $customRules = [];

    abstract public function create(string $ruleName, string $display, mixed $inputValue, mixed $ruleValue, string $fieldName): ?AbstractValidator;

    public function run(string $ruleName, string $display, mixed $inputValue, mixed $ruleValue, string $fieldName): array|string|bool
    {
        $validator = $this->create($ruleName, $display, $inputValue, $ruleValue, $fieldName);

        if ($validator === null) {
            throw ValidationException::validatorNotInitialized(); // Rule not applicable
        }
        return $validator->validate();
    }

    /**
     * Add a custom validation rule.
     */
    public function addCustomRule(string $ruleName, callable $validator): void
    {
        $this->customRules[$ruleName] = $validator;
    }

    /**
     * Check if a custom rule exists.
     */
    public function hasCustomRule(string $ruleName): bool
    {
        return isset($this->customRules[$ruleName]);
    }

    public function isSimpleRule(string $ruleName): bool
    {
        return in_array($ruleName, self::SIMPLE_RULES);
    }

    /**
     * Execute a custom rule.
     */
    public function executeCustomRule(string $ruleName, string $display, mixed $inputValue, mixed $ruleValue): string|bool
    {
        if (!$this->hasCustomRule($ruleName)) {
            throw new InvalidArgumentException("Custom rule '{$ruleName}' not found");
        }

        return call_user_func($this->customRules[$ruleName], $display, $inputValue, $ruleValue);
    }
}