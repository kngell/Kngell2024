<?php

declare(strict_types=1);

/**
 * Phone number validator with international format support.
 */
class PhoneValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'The %s field must be a valid phone number';

    private const array PHONE_PATTERNS = [
        'us' => '/^\+?1?[-.\s]?\(?([0-9]{3})\)?[-.\s]?([0-9]{3})[-.\s]?([0-9]{4})$/',
        'uk' => '/^\+?44[-.\s]?([0-9]{4})[-.\s]?([0-9]{6})$/',
        'international' => '/^\+?[1-9]\d{1,14}$/', // E.164 format
        'simple' => '/^[\+]?[0-9\s\-\(\)]{10,15}$/', // Basic format
    ];

    public function __construct(
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly mixed $ruleValue
    ) {
    }

    public function validate(): string|bool
    {
        if (empty($this->inputValue)) {
            return false; // Let RequiredValidator handle empty values
        }

        if (! is_string($this->inputValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }

        $format = is_string($this->ruleValue) ? $this->ruleValue : 'simple';

        if (! $this->isValidPhoneNumber($this->inputValue, $format)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }

        return false;
    }

    private function isValidPhoneNumber(string $phone, string $format): bool
    {
        $cleanPhone = $this->cleanPhoneNumber($phone);

        if (isset(self::PHONE_PATTERNS[$format])) {
            return preg_match(self::PHONE_PATTERNS[$format], $cleanPhone) === 1;
        }

        // Default to simple validation
        return preg_match(self::PHONE_PATTERNS['simple'], $cleanPhone) === 1;
    }

    private function cleanPhoneNumber(string $phone): string
    {
        // Remove common separators but keep + for international numbers
        return preg_replace('/[^\+0-9]/', '', $phone);
    }
}