<?php

declare(strict_types=1);

final readonly class ButtonConfig
{
    /**
     * @param array<string, mixed> $attributes
     * @param string[] $classes
     */
    public function __construct(
        public string $type = 'button',
        public string $label = '',
        public string $size = 'md-compact',
        public string $ariaLabel = '',
        public string $style = 'primary',
        public ?string $icon = null,
        public string $iconPosition = 'left',
        public array $attributes = [],
        public array $classes = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->label,
            'size' => $this->size,
            'ariaLabel' => $this->ariaLabel,
            'style' => $this->style,
            'icon' => $this->icon,
            'iconPosition' => $this->iconPosition,
            'attributes' => $this->attributes,
            'class' => $this->classes,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'] ?? 'button',
            label: $data['label'] ?? '',
            size: $data['size'] ?? 'md-compact',
            ariaLabel: $data['ariaLabel'] ?? $data['label'] ?? '',
            style: $data['style'] ?? 'primary',
            icon: $data['icon'] ?? null,
            iconPosition: $data['iconPosition'] ?? 'left',
            attributes: $data['attributes'] ?? [],
            classes: $data['class'] ?? $data['classes'] ?? [],
        );
    }
}