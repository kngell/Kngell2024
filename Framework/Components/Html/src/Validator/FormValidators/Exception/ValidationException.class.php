<?php

declare(strict_types=1);

/**
 * Exception thrown when validation configuration or execution fails.
 */
class ValidationException extends Exception
{
    // Error type constants
    public const RULES_FILE_NOT_FOUND = 1001;
    public const INVALID_RULES_FORMAT = 1002;
    public const VALIDATOR_CREATOR_NOT_FOUND = 1003;
    public const VALIDATION_FAILED = 1004;
    public const VALIDATOR_NOT_INITIALIZED = 1005;
    public const INVALID_RULE_CONFIGURATION = 1006;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        private readonly ?ValidationResult $validationResult = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getValidationResult(): ?ValidationResult
    {
        return $this->validationResult;
    }

    public static function rulesFileNotFound(string $filename): self
    {
        return new self(
            "Validation rules file not found: {$filename}",
            self::RULES_FILE_NOT_FOUND,
        );
    }

    public static function invalidRulesFormat(string $filename): self
    {
        return new self(
            "Invalid validation rules format in file: {$filename}",
            self::INVALID_RULES_FORMAT,
        );
    }

    public static function validatorCreatorNotFound(string $ruleName): self
    {
        return new self(
            "Validator creator not found for rule: {$ruleName}",
            self::VALIDATOR_CREATOR_NOT_FOUND,
        );
    }

    public static function validatorCreatorNotInitialized(): self
    {
        return new self(
            'Validator creator creator not initialized',
            self::VALIDATOR_NOT_INITIALIZED,
        );
    }

    public static function validatorNotInitialized(): self
    {
        return new self(
            'Validator creator not initialized',
            self::VALIDATOR_NOT_INITIALIZED,
        );
    }

    public static function invalidRuleConfiguration(string $ruleName, string $details = ''): self
    {
        $message = "Invalid configuration for rule: {$ruleName}";
        if ($details) {
            $message .= " - {$details}";
        }
        return new self($message, self::INVALID_RULE_CONFIGURATION);
    }

    public static function withValidationResult(ValidationResult $result): self
    {
        return new self(
            'Validation failed',
            self::VALIDATION_FAILED,
            null,
            $result,
        );
    }

    public function isValidationFailure(): bool
    {
        return $this->code === self::VALIDATION_FAILED && $this->validationResult !== null;
    }

    public function isConfigurationError(): bool
    {
        return in_array($this->code, [
            self::RULES_FILE_NOT_FOUND,
            self::INVALID_RULES_FORMAT,
            self::VALIDATOR_CREATOR_NOT_FOUND,
            self::INVALID_RULE_CONFIGURATION,
        ], true);
    }
}