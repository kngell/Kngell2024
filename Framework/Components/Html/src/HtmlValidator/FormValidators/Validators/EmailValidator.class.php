<?php

declare(strict_types=1);

class EmailValidator extends AbstractValidator
{
    private const string ERROR_MESSAGE = 'The %s field is not a valid email format';
    private const int MAX_EMAIL_LENGTH = 254; // RFC 5321 limit

    public function __construct(
        private readonly string $display,
        private readonly mixed $inputValue,
        private readonly mixed $ruleValue
    ) {
    }

    public function validate(): string|bool
    {
        // Skip validation if field is empty (let RequiredValidator handle it)
        if (empty($this->inputValue)) {
            return false;
        }

        if (! is_string($this->inputValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }

        if (! $this->isValidEmailFormat($this->inputValue)) {
            return $this->erroMessage(sprintf(self::ERROR_MESSAGE, $this->display));
        }

        return false;
    }

    private function isValidEmailFormat(string $email): bool
    {
        // Check length
        if (strlen($email) > self::MAX_EMAIL_LENGTH) {
            return false;
        }

        // Basic format validation
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Additional checks for common issues
        if (! $this->hasValidDomainPart($email)) {
            return false;
        }

        return true;
    }

    private function hasValidDomainPart(string $email): bool
    {
        $atPosition = strrpos($email, '@');
        if ($atPosition === false) {
            return false;
        }

        $domain = substr($email, $atPosition + 1);

        // Check if domain has at least one dot
        if (strpos($domain, '.') === false) {
            return false;
        }

        // Optional: Check DNS record (can be slow, so make it configurable)
        if ($this->ruleValue === 'strict' || $this->ruleValue === true) {
            return $this->checkDnsRecord($domain);
        }

        return true;
    }

    private function checkDnsRecord(string $domain): bool
    {
        try {
            return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
        } catch (Throwable) {
            // If DNS check fails, don't fail validation
            return true;
        }
    }
}