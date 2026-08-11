<?php

declare(strict_types=1);

final readonly class HeaderButton
{
    /**
     * @param array<string, string> $attributes
     * @param string[]              $class
     */
    public function __construct(
        public string $label,
        public string $action,
        public string $formName,
        public string $method = 'POST',
        public string $ariaLabel = '',
        public string $type = 'submit',
        public string $style = 'primary',
        public string $size = 'md-compact',
        public ?string $icon = null,
        public string $iconPosition = 'left',
        public ?string $blockType = null,
        public bool $requiresEditMode = false,
        public bool $requiresEntityId = false,
        public array $attributes = [],
        public array $class = [],
    ) {
    }

    /**
     * Returns a copy with a different action URL.
     * Used by decorators to override the delete action at runtime.
     */
    public function withAction(string $action): self
    {
        return new self(
            label: $this->label,
            action: $action,
            formName: $this->formName,
            method: $this->method,
            ariaLabel: $this->ariaLabel,
            type: $this->type,
            style: $this->style,
            size: $this->size,
            icon: $this->icon,
            iconPosition: $this->iconPosition,
            requiresEditMode: $this->requiresEditMode,
            requiresEntityId: $this->requiresEntityId,
            attributes: $this->attributes,
            class: $this->class,
            blockType: $this->blockType,
        );
    }

    /**
     * Convert to the array shape consumed by HeaderButtonConfig::fromArray().
     * Bridges typed objects into the existing array-based pipeline.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'action' => $this->action,
            'formName' => $this->formName,
            'requiresEditMode' => $this->requiresEditMode,
            'requiresEntityId' => $this->requiresEntityId,
            'method' => $this->method,
            'type' => $this->type,
            'label' => $this->label,
            'size' => $this->size,
            'ariaLabel' => $this->ariaLabel !== '' ? $this->ariaLabel : $this->label,
            'style' => $this->style,
            'icon' => $this->icon,
            'iconPosition' => $this->iconPosition,
            'attributes' => $this->attributes,
            'class' => $this->class,
            'blockType' => $this->blockType,
        ];
    }
}