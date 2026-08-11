<?php

declare(strict_types=1);

final class ButtonConfig
{
    public const ICON_POSITION_LEFT = 'left';
    public const ICON_POSITION_RIGHT = 'right';
    public const ICON_POSITION_TOP = 'top';
    public const ICON_POSITION_BOTTOM = 'bottom';

    /**
     * @param string $type Button type (submit, button, reset)
     * @param string $label Button label text
     * @param string $size Button size (sm, md, lg, etc.)
     * @param string $style Button style (primary, secondary, danger, etc.)
     * @param string $ariaLabel Aria label for accessibility
     * @param IconConfig|null $iconConfig Icon configuration
     * @param string $iconPosition Icon position (left, right, top, bottom)
     * @param string|null $id Button ID
     * @param array<string, string|int|bool> $attributes Additional HTML attributes
     * @param array<string> $buttonClass Additional button CSS classes
     * @param bool $iconOnly Whether button is icon-only
     */
    public function __construct(
        public string $type = 'button',
        public string $label = '',
        public string $size = 'md-compact',
        public string $style = 'primary',
        public string $ariaLabel = '',
        public ?IconConfig $iconConfig = null,
        public string $iconPosition = self::ICON_POSITION_LEFT,
        public ?string $id = null,
        public array $attributes = [],
        public array $buttonClass = [],
        public bool $iconOnly = false,
    ) {
        if (empty($this->ariaLabel) && !empty($this->label)) {
            $this->ariaLabel = $this->label;
        }
        if ($this->iconOnly && empty($this->ariaLabel)) {
            throw new InvalidArgumentException('Icon-only buttons must have an aria label');
        }
    }

    public function setIcon(IconConfig|string $icon, string $position = self::ICON_POSITION_LEFT): self
    {
        if (is_string($icon)) {
            $icon = IconConfig::create($icon, $this->ariaLabel ?: $this->label);
        }

        $this->iconConfig = $icon;
        $this->iconPosition = $position;
        return $this;
    }

    public function setIconPosition(string $position): self
    {
        $this->iconPosition = $position;
        return $this;
    }

    public function setIconOnly(bool $iconOnly = true): self
    {
        $this->iconOnly = $iconOnly;
        if ($iconOnly && empty($this->ariaLabel) && $this->iconConfig !== null) {
            $this->ariaLabel = $this->iconConfig->ariaLabel ?: $this->label;
        }
        return $this;
    }

    /**
     * Set button style (fluent setter).
     */
    public function setStyle(string $style): self
    {
        $this->style = $style;
        return $this;
    }

    /**
     * Set button size (fluent setter).
     */
    public function setSize(string $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Set button label (fluent setter).
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        if (empty($this->ariaLabel)) {
            $this->ariaLabel = $label;
        }
        return $this;
    }

    /**
     * Check if button has an icon.
     */
    public function hasIcon(): bool
    {
        return $this->iconConfig !== null && !empty($this->iconConfig->icon);
    }

    /**
     * Check if button has a label.
     */
    public function hasLabel(): bool
    {
        return !empty($this->label);
    }

    /**
     * Check if button should render as icon-only.
     */
    public function isIconOnly(): bool
    {
        return $this->iconOnly || ($this->hasIcon() && !$this->hasLabel());
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'label' => $this->label,
            'size' => $this->size,
            'style' => $this->style,
            'ariaLabel' => $this->ariaLabel,
            'iconConfig' => $this->iconConfig?->toArray(),
            'iconPosition' => $this->iconPosition,
            'id' => $this->id,
            'attributes' => $this->attributes,
            'buttonClass' => $this->buttonClass,
            'iconOnly' => $this->iconOnly,
        ];
    }

    /**
     * Merge with another config or overrides.
     */
    public function merge(array|self $overrides): self
    {
        if ($overrides instanceof self) {
            $overrides = $overrides->toArray();
        }

        return new self(
            type: $overrides['type'] ?? $this->type,
            label: $overrides['label'] ?? $this->label,
            size: $overrides['size'] ?? $this->size,
            style: $overrides['style'] ?? $this->style,
            ariaLabel: $overrides['ariaLabel'] ?? $this->ariaLabel,
            iconConfig: $overrides['iconConfig'] ?? $this->iconConfig,
            iconPosition: $overrides['iconPosition'] ?? $this->iconPosition,
            id: $overrides['id'] ?? $this->id,
            attributes: array_merge($this->attributes, $overrides['attributes'] ?? []),
            buttonClass: array_merge($this->buttonClass, $overrides['buttonClass'] ?? []),
            iconOnly: $overrides['iconOnly'] ?? $this->iconOnly,
        );
    }

    /**
     * Create a copy with modifications.
     */
    public function with(array $overrides): self
    {
        return $this->merge($overrides);
    }

    /**
     * Create a text-only button.
     */
    public static function text(string $label, string $style = 'primary', string $size = 'md-compact'): self
    {
        return new self(
            label: $label,
            size: $size,
            style: $style,
        );
    }

    /**
     * Create a button with icon.
     */
    public static function withIcon(
        string $icon,
        string $label = '',
        string $style = 'primary',
        string $size = 'md-compact',
        string $iconPosition = self::ICON_POSITION_LEFT,
        string $ariaLabel = '',
    ): self {
        return new self(
            label: $label,
            size: $size,
            style: $style,
            ariaLabel: $ariaLabel ?: ($label ?: $icon),
            iconConfig: IconConfig::create($icon, $ariaLabel ?: ($label ?: $icon)),
            iconPosition: $iconPosition,
        );
    }

    /**
     * Create an icon-only button.
     */
    public static function iconOnly(
        string $icon,
        string $ariaLabel,
        string $style = 'primary',
        string $size = 'md-compact',
    ): self {
        return new self(
            size: $size,
            style: $style,
            ariaLabel: $ariaLabel,
            iconConfig: IconConfig::create($icon, $ariaLabel),
            iconOnly: true,
        );
    }

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        $iconConfig = null;
        if (isset($data['iconConfig'])) {
            if ($data['iconConfig'] instanceof IconConfig) {
                $iconConfig = $data['iconConfig'];
            } elseif (is_array($data['iconConfig'])) {
                $iconConfig = new IconConfig(
                    icon: $data['iconConfig']['icon'] ?? '',
                    ariaLabel: $data['iconConfig']['ariaLabel'] ?? '',
                    iconClass: $data['iconConfig']['iconClass'] ?? [],
                    desc: $data['iconConfig']['desc'] ?? null,
                    role: $data['iconConfig']['role'] ?? 'img',
                    title: $data['iconConfig']['title'] ?? null,
                    width: $data['iconConfig']['width'] ?? null,
                    height: $data['iconConfig']['height'] ?? null,
                    viewBox: $data['iconConfig']['viewBox'] ?? '0 0 24 24',
                    fill: $data['iconConfig']['fill'] ?? null,
                    stroke: $data['iconConfig']['stroke'] ?? null,
                    strokeWidth: $data['iconConfig']['strokeWidth'] ?? null,
                    wrapperClass: $data['iconConfig']['wrapperClass'] ?? null,
                );
            }
        }

        // Handle legacy 'icon' field for backward compatibility
        if ($iconConfig === null && isset($data['icon'])) {
            $iconConfig = IconConfig::create($data['icon']);
        }

        return new self(
            type: $data['type'] ?? 'button',
            label: $data['label'] ?? '',
            size: $data['size'] ?? 'md-compact',
            style: $data['style'] ?? 'primary',
            ariaLabel: $data['ariaLabel'] ?? $data['label'] ?? '',
            iconConfig: $iconConfig,
            iconPosition: $data['iconPosition'] ?? self::ICON_POSITION_LEFT,
            id: $data['id'] ?? null,
            attributes: $data['attributes'] ?? [],
            buttonClass: $data['buttonClass'] ?? $data['class'] ?? [],
            iconOnly: $data['iconOnly'] ?? false,
        );
    }
}