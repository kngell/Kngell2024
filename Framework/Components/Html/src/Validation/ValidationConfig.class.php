<?php

declare(strict_types=1);

final class ValidationConfig
{
    public function __construct(
        private bool $sanitizeInput = true,
        private bool $stopOnFirstError = false,
        private bool $validateAllFields = false,
        private bool $skipMissingFields = true,
        private array $validationGroups = [],
        private bool $sanitizeHtml = true,
        private array $messages = [],
        private ?ValidationMessageService $messageService = null,
    ) {
        // If no message service is provided, create a default one
        if ($this->messageService === null) {
            $this->messageService = new ValidationMessageService([], App::diget(SessionInterface::class));
        }
    }

    // Getter methods
    public function shouldSanitizeInput(): bool
    {
        return $this->sanitizeInput;
    }

    public function shouldStopOnFirstError(): bool
    {
        return $this->stopOnFirstError;
    }

    public function shouldValidateAllFields(): bool
    {
        return $this->validateAllFields;
    }

    public function shouldSkipMissingFields(): bool
    {
        return $this->skipMissingFields;
    }

    public function shouldSanitizeHtml(): bool
    {
        return $this->sanitizeHtml;
    }

    public function hasValidationGroups(): bool
    {
        return !empty($this->validationGroups);
    }

    public function getValidationGroups(): array
    {
        return $this->validationGroups;
    }

    public function getMessage(string $ruleName): string
    {
        return $this->messageService->getMessage($ruleName);
    }

    public function getHintClasses(): array
    {
        return $this->messageService->getHintClasses();
    }

    public function getErrorClasses(): array
    {
        return $this->messageService->getErrorClasses();
    }

    public function formatMessage(string $ruleName, array $params): string
    {
        return $this->messageService->formatMessage($ruleName, $params);
    }

    public function getMessageService(): ValidationMessageService
    {
        return $this->messageService;
    }

    public function getMessages(): array
    {
        return $this->messageService->getAllMessages();
    }

    // Fluent interface for configuration
    public function withSanitizeInput(bool $sanitize): self
    {
        $new = clone $this;
        $new->sanitizeInput = $sanitize;
        return $new;
    }

    public function withStopOnFirstError(bool $stopOnError): self
    {
        $new = clone $this;
        $new->stopOnFirstError = $stopOnError;
        return $new;
    }

    public function withValidateAllFields(bool $validateAll): self
    {
        $new = clone $this;
        $new->validateAllFields = $validateAll;
        return $new;
    }

    public function withSkipMissingFields(bool $skipMissing): self
    {
        $new = clone $this;
        $new->skipMissingFields = $skipMissing;
        return $new;
    }

    public function withSanitizeHtml(bool $sanitizeHtml): self
    {
        $new = clone $this;
        $new->sanitizeHtml = $sanitizeHtml;
        return $new;
    }

    public function withValidationGroups(array $groups): self
    {
        $new = clone $this;
        $new->validationGroups = $groups;
        return $new;
    }

    public function withMessages(array $messages): self
    {
        $new = clone $this;
        $new->messages = $messages;
        return $new;
    }

    public function withMessage(string $ruleName, string $message): self
    {
        $new = clone $this;
        $new->messages[$ruleName] = $message;
        return $new;
    }

    public function withMessageService(ValidationMessageService $messageService): self
    {
        $new = clone $this;
        $new->messageService = $messageService;
        return $new;
    }

    // Array representation for serialization
    public function toArray(): array
    {
        return [
            'sanitizeInput' => $this->sanitizeInput,
            'stopOnFirstError' => $this->stopOnFirstError,
            'validateAllFields' => $this->validateAllFields,
            'skipMissingFields' => $this->skipMissingFields,
            'validationGroups' => $this->validationGroups,
            'sanitizeHtml' => $this->sanitizeHtml,
            'messages' => $this->messages,
        ];
    }

    public static function default(): self
    {
        return new self(
            sanitizeInput: true,
            stopOnFirstError: false,
            validateAllFields: false,
            skipMissingFields: true,
            sanitizeHtml: true,
        );
    }

    public static function stopOnFirstError(): self
    {
        return new self(
            sanitizeInput: true,
            stopOnFirstError: true,
            validateAllFields: false,
            skipMissingFields: true,
            sanitizeHtml: true,
        );
    }

    public static function withGroups(array $groups): self
    {
        return new self(
            sanitizeInput: true,
            stopOnFirstError: false,
            validateAllFields: false,
            skipMissingFields: true,
            validationGroups: $groups,
            sanitizeHtml: true,
        );
    }

    public static function strict(): self
    {
        return new self(
            sanitizeInput: true,
            stopOnFirstError: false,
            validateAllFields: true,
            skipMissingFields: false,
            sanitizeHtml: true,
        );
    }

    public static function lenient(): self
    {
        return new self(
            sanitizeInput: false,
            stopOnFirstError: false,
            validateAllFields: false,
            skipMissingFields: true,
            sanitizeHtml: false,
        );
    }

    // Create from array with message service
    public static function fromArray(array $config, ?ValidationMessageService $messageService = null): self
    {
        return new self(
            sanitizeInput: $config['sanitizeInput'] ?? true,
            stopOnFirstError: $config['stopOnFirstError'] ?? false,
            validateAllFields: $config['validateAllFields'] ?? false,
            skipMissingFields: $config['skipMissingFields'] ?? true,
            validationGroups: $config['validationGroups'] ?? [],
            sanitizeHtml: $config['sanitizeHtml'] ?? true,
            messages: $config['messages'] ?? [],
            messageService: $messageService,
        );
    }
}