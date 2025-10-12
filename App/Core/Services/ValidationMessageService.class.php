<?php

declare(strict_types=1);

final class ValidationMessageService
{
    public function __construct(
        private readonly array $validationConfig,
        private readonly SessionInterface $session,
    ) {
        // Validate that we have the expected structure
        if (empty($this->validationConfig)) {
            throw new InvalidArgumentException('Validation configuration is empty');
        }
    }

    public function getMessage(string $rule): string
    {
        $locale = $this->getCurrentLocale();
        $messages = $this->validationConfig['messages'] ?? $this->getDefaultMessages();
        $message = $messages[$rule] ?? $messages['default'] ?? '%s is invalid.';

        return $this->localizeMessage($message, $locale);
    }

    public function getHintClasses(): array
    {
        return $this->validationConfig['classes']['hint'] ?? ['input-box__hint-text', 'invalid-feedback'];
    }

    public function getErrorClasses(): array
    {
        return $this->validationConfig['classes']['error'] ?? ['error-message', 'text-danger'];
    }

    public function formatMessage(string $rule, array $params): string
    {
        $message = $this->getMessage($rule);

        foreach ($params as $key => $value) {
            if (is_int($key)) {
                $message = preg_replace('/%[sdf]/', (string) $value, $message, 1);
            } else {
                $message = str_replace("{{$key}}", (string) $value, $message);
            }
        }

        return $message;
    }

    public function getAllMessages(): array
    {
        return $this->validationConfig['messages'] ?? $this->getDefaultMessages();
    }

    public function getConfig(): array
    {
        return $this->validationConfig;
    }

    private function getCurrentLocale(): string
    {
        if ($this->session->exists('locale')) {
            $locale = $this->session->get('locale');
            return is_string($locale) ? $locale : 'en';
        }
        return 'en';
    }

    private static function localizeMessage(string $message, string $locale): string
    {
        // Simple localization - you can extend this with translation files
        $translations = [
            'fr' => [
                '%s is required.' => '%s est requis.',
                '%s must be at least %s characters.' => '%s doit avoir au moins %s caractères.',
                '%s must be at most %s characters.' => '%s doit avoir au maximum %s caractères.',
                '%s format is invalid.' => 'Le format de %s est invalide.',
                // Add more translations as needed
            ],
            'es' => [
                '%s is required.' => '%s es requerido.',
                '%s must be at least %s characters.' => '%s debe tener al menos %s caracteres.',
                // Add more translations as needed
            ],
        ];

        return $translations[$locale][$message] ?? $message;
    }

    private function getDefaultMessages(): array
    {
        return [
            'required' => '%s is required.',
            'min' => '%s must be at least %s characters.',
            'max' => '%s must be at most %s characters.',
            'pattern' => '%s format is invalid.',
            'numeric' => '%s must be a number.',
            'min_value' => '%s must be at least %s.',
            'max_value' => '%s must be at most %s.',
            'lte' => '%s must be less than or equal to %s.',
            'gte' => '%s must be greater than or equal to %s.',
            'required_if' => '%s is required when %s is present.',
            'password_match' => 'Passwords do not match.',
            'email' => '%s must be a valid email address.',
            'url' => '%s must be a valid URL.',
            'date' => '%s must be a valid date.',
            'boolean' => '%s must be true or false.',
            'default' => '%s is invalid.',
        ];
    }
}