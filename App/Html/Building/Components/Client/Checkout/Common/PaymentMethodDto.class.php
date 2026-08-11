<?php

declare(strict_types=1);

class PaymentMethodDto
{
    /**
     * @param string $id Unique identifier for the payment method
     * @param string $label Display label
     * @param string $value Radio value
     * @param array<string> $icons Icon names for display
     * @param bool $isDefault Whether this is the default selection
     * @param array<FormFieldConfig>|null $fields Form fields for card payment
     * @param string|null $content HTML content for non-card methods
     * @param string|null $description Short description shown inline
     * @param bool $isExpandable Whether content expands on selection
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $value,
        public readonly array $icons = [],
        public readonly bool $isDefault = false,
        public readonly ?array $fields = null,
        public readonly ?string $content = null,
        public readonly ?string $description = null,
        public readonly bool $isExpandable = true,
    ) {
    }

    /**
     * Check if this payment method has form fields (like card).
     */
    public function hasFields(): bool
    {
        return $this->fields !== null && !empty($this->fields);
    }

    /**
     * Check if this payment method has content (like PayPal description).
     */
    public function hasContent(): bool
    {
        return $this->content !== null && !empty($this->content);
    }

    /**
     * Check if this payment method has a description.
     */
    public function hasDescription(): bool
    {
        return $this->description !== null && !empty($this->description);
    }

    /**
     * Create a card payment method.
     */
    public static function createCard(
        string $id = 'card',
        string $label = 'Credit / Debit Card',
        string $value = 'card',
        array $icons = ['visa', 'mastercard', 'google-pay'],
        bool $isDefault = true,
        array $fields = [],
        ?string $description = null,
        bool $isExpandable = true,
    ): self {
        return new self(
            id: $id,
            label: $label,
            value: $value,
            icons: $icons,
            isDefault: $isDefault,
            fields: $fields,
            description: $description,
            isExpandable: $isExpandable,
        );
    }

    /**
     * Create a simple payment method (like PayPal, Apple Pay).
     */
    public static function createSimple(
        string $id,
        string $label,
        string $value,
        string $content,
        array $icons = [],
        bool $isDefault = false,
        ?string $description = null,
        bool $isExpandable = true,
    ): self {
        return new self(
            id: $id,
            label: $label,
            value: $value,
            icons: $icons,
            isDefault: $isDefault,
            content: $content,
            description: $description,
            isExpandable: $isExpandable,
        );
    }
}