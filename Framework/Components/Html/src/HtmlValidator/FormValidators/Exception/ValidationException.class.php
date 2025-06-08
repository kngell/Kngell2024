<?php

declare(strict_types=1);

/**
 * Exception thrown when validation configuration or execution fails.
 */
class ValidationException extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
        private readonly ?ValidationResult $validationResult = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getValidationResult(): ?ValidationResult
    {
        return $this->validationResult;
    }

    public static function rulesFileNotFound(string $filename): self
    {
        return new self("Validation rules file not found: {$filename}");
    }

    public static function invalidRulesFormat(string $filename): self
    {
        return new self("Invalid validation rules format in file: {$filename}");
    }

    public static function validatorCreatorNotFound(string $ruleName): self
    {
        return new self("Validator creator not found for rule: {$ruleName}");
    }

    public static function withValidationResult(ValidationResult $result): self
    {
        return new self('Validation failed', 0, null, $result);
    }
}
