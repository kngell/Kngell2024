<?php

declare(strict_types=1);

final class AddToCartConfig
{
    public function __construct(
        public readonly string $action = '/cart/add',
        public readonly string $method = 'POST',
        public readonly string $itemIdField = 'product_id',
        public readonly bool $includeCsrf = true,
        public readonly array $additionalHiddenFields = [],
        public readonly array $formAttributes = [],
        public readonly array $formClasses = ['add-to-cart-form'],
        public readonly ?string $redirectUrl = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'method' => $this->method,
            'itemIdField' => $this->itemIdField,
            'includeCsrf' => $this->includeCsrf,
            'additionalHiddenFields' => $this->additionalHiddenFields,
            'formAttributes' => $this->formAttributes,
            'formClasses' => $this->formClasses,
            'redirectUrl' => $this->redirectUrl,
        ];
    }

    public static function create(
        string $action = '/admin/user-cart/add-item',
        string $method = 'post',
        string $itemIdField = 'product_id',
        bool $includeCsrf = true,
        array $additionalHiddenFields = [],
        array $formAttributes = [],
        array $formClasses = ['add-to-cart-form'],
        ?string $redirectUrl = null,
    ): self {
        return new self(
            action: $action,
            method: $method,
            itemIdField: $itemIdField,
            includeCsrf: $includeCsrf,
            additionalHiddenFields: $additionalHiddenFields,
            formAttributes: $formAttributes,
            formClasses: $formClasses,
            redirectUrl: $redirectUrl,
        );
    }

    public static function default(): self
    {
        return new self();
    }

    public static function withAction(string $action): self
    {
        return new self(action: $action);
    }

    public static function ajaxOnly(): self
    {
        return new self(
            formAttributes: ['data-ajax' => 'true', 'data-ajax-only' => 'true'],
        );
    }
}