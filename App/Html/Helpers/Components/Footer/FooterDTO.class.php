<?php

declare(strict_types=1);

final readonly class FooterDTO
{
    public function __construct(
        public ?string $formId = null,
        public array $footerClass = ['form__footer'],
        public bool $renderProgressBar = false,
        public int $completionPercentage = 0,
        public string $action = '#',
        public string $cancelRoute = '#',
        public string $method = 'POST',
        public bool $wrapWithForm = false,
        public ?ButtonConfig $cancelButtonConfig = null,
        public ?ButtonConfig $submitButtonConfig = null,
        public string $submitText = 'Submit',
        public string $submitIcon = 'icon-plus',
        public string $submitStyle = 'primary',
    ) {
    }

    /**
     * Determines if the submit button needs external form wrapping.
     *
     * - If formId is set: button uses form="" attribute, no wrapper needed
     * - If wrapWithForm: button gets its own <form> wrapper
     * - Otherwise: bare button (must be inside a form already)
     */
    public function submitNeedsWrapper(): bool
    {
        return $this->formId === null && $this->wrapWithForm;
    }

    public function getCancelButtonConfig(): ButtonConfig
    {
        return $this->cancelButtonConfig ?? new ButtonConfig(
            type: $this->wrapWithForm ? 'submit' : 'button',
            label: 'Cancel',
            size: 'md-compact',
            ariaLabel: 'Cancel',
            style: 'outlined',
            icon: 'icon-cancel',
            attributes: [
                'data-modal-cancel' => 'true',
            ],
        );
    }

    public function getSubmitButtonConfig(): ButtonConfig
    {
        if ($this->submitButtonConfig !== null) {
            return $this->ensureFormId($this->submitButtonConfig);
        }

        $attributes = [];
        if ($this->formId !== null) {
            $attributes['form'] = $this->formId;
        }

        return new ButtonConfig(
            type: 'submit',
            label: $this->submitText,
            size: 'md-compact',
            ariaLabel: $this->submitText,
            style: $this->submitStyle,
            icon: $this->submitIcon,
            attributes: $attributes,
        );
    }

    private function ensureFormId(ButtonConfig $config): ButtonConfig
    {
        if ($this->formId === null) {
            return $config;
        }

        if (isset($config->attributes['form'])) {
            return $config;
        }

        return new ButtonConfig(
            type: $config->type,
            label: $config->label,
            size: $config->size,
            ariaLabel: $config->ariaLabel,
            style: $config->style,
            icon: $config->icon,
            iconPosition: $config->iconPosition,
            attributes: array_merge($config->attributes, ['form' => $this->formId]),
            classes: $config->classes,
        );
    }

    public static function forInlineForm(
        ?string $formId = null,
        array $footerClass = ['form__footer'],
        bool $renderProgressBar = false,
        int $completionPercentage = 0,
        string $submitText = 'Submit',
        string $submitIcon = 'icon-plus',
        string $submitStyle = 'primary',
        ?ButtonConfig $submitButtonConfig = null,
        ?ButtonConfig $cancelButtonConfig = null,
    ): self {
        return new self(
            formId: $formId,
            footerClass: $footerClass,
            renderProgressBar: $renderProgressBar,
            completionPercentage: $completionPercentage,
            wrapWithForm: false,
            submitText: $submitText,
            submitIcon: $submitIcon,
            submitStyle: $submitStyle,
            submitButtonConfig: $submitButtonConfig,
            cancelButtonConfig: $cancelButtonConfig,
        );
    }

    public static function forStandalone(
        string $action,
        string $cancelRoute,
        string $method = 'POST',
        ?string $formId = null,
        array $footerClass = ['form__footer'],
        string $submitText = 'Submit',
        string $submitIcon = 'icon-plus',
        string $submitStyle = 'primary',
        ?ButtonConfig $cancelButtonConfig = null,
        ?ButtonConfig $submitButtonConfig = null,
    ): self {
        return new self(
            formId: $formId,
            footerClass: $footerClass,
            action: $action,
            cancelRoute: $cancelRoute,
            method: $method,
            wrapWithForm: true,
            submitText: $submitText,
            submitIcon: $submitIcon,
            submitStyle: $submitStyle,
            cancelButtonConfig: $cancelButtonConfig,
            submitButtonConfig: $submitButtonConfig,
        );
    }
}