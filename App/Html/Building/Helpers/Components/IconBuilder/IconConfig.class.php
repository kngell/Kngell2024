<?php

declare(strict_types=1);

class IconConfig
{
    private const string DEFAULT_ICON = 'icon-default';

    private readonly string $normalizedIcon;

    /**
     * @param string $icon The icon name/identifier or path (e.g., "/icons/github.svg")
     * @param string $ariaLabel The aria-label for accessibility
     * @param array<string> $iconClass Additional CSS classes
     * @param string|null $desc Optional description for screen readers
     * @param string $role ARIA role (default: 'img')
     * @param string|null $title Optional title element
     * @param int|null $width Optional width in pixels
     * @param int|null $height Optional height in pixels
     * @param string $viewBox ViewBox attribute
     * @param string|null $fill Optional fill color
     * @param string|null $stroke Optional stroke color
     * @param string|null $strokeWidth Optional stroke width
     */
    public function __construct(
        public readonly string $icon,
        public readonly string $ariaLabel,
        public readonly array $iconClass = [],
        public readonly ?string $desc = null,
        public readonly string $role = 'img',
        public readonly ?string $title = null,
        public readonly ?int $width = null,
        public readonly ?int $height = null,
        public readonly string $viewBox = '0 0 24 24',
        public readonly ?string $fill = null,
        public readonly ?string $stroke = null,
        public readonly ?string $strokeWidth = null,
        public readonly ?string $wrapperClass = null,
        public readonly array $aria = [],
    ) {
        $this->normalizedIcon = $this->normalizeIconName($this->icon);

        // Only validate if we have a normalized icon name
        if (!empty($this->normalizedIcon)) {
            if (!preg_match('/^[a-zA-Z0-9_-]+$/', $this->normalizedIcon)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Icon name must be a valid identifier, got: "%s" (normalized: "%s")',
                        $this->icon,
                        $this->normalizedIcon,
                    ),
                );
            }
        }
        // If empty, we'll use the default icon in getIconName()
    }

    /**
     * Get the normalized icon name with fallback to default.
     */
    public function getIconName(): string
    {
        return !empty($this->normalizedIcon)
            ? $this->normalizedIcon
            : self::DEFAULT_ICON;
    }

    /**
     * Check if the icon has a valid name (not using default fallback).
     */
    public function hasValidIcon(): bool
    {
        return !empty($this->normalizedIcon) &&
               $this->normalizedIcon !== self::DEFAULT_ICON;
    }

    public function merge(array|self $overrides): self
    {
        if ($overrides instanceof self) {
            $overrides = $overrides->toArray();
        }

        return new self(
            icon: $overrides['icon'] ?? $this->icon,
            ariaLabel: $overrides['ariaLabel'] ?? $this->ariaLabel,
            iconClass: array_merge($this->iconClass, $overrides['iconClass'] ?? []),
            desc: $overrides['desc'] ?? $this->desc,
            role: $overrides['role'] ?? $this->role,
            title: $overrides['title'] ?? $this->title,
            width: $overrides['width'] ?? $this->width,
            height: $overrides['height'] ?? $this->height,
            viewBox: $overrides['viewBox'] ?? $this->viewBox,
            fill: $overrides['fill'] ?? $this->fill,
            stroke: $overrides['stroke'] ?? $this->stroke,
            strokeWidth: $overrides['strokeWidth'] ?? $this->strokeWidth,
            wrapperClass: $overrides['wrapperClass'] ?? $this->wrapperClass,
            aria: $overrides['aria'] ?? $this->aria,
        );
    }

    public function getClassString(): string
    {
        return implode(' ', $this->iconClass);
    }

    public function isDecorative(): bool
    {
        return $this->role === 'presentation' || empty($this->ariaLabel);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    private function normalizeIconName(string $icon): string
    {
        // Trim whitespace
        $icon = trim($icon);

        // If empty, return empty string (will use default)
        if (empty($icon)) {
            return '';
        }

        // Extract filename without path and extension
        $icon = pathinfo($icon, PATHINFO_FILENAME);

        // If extraction resulted in empty, return empty
        if (empty($icon)) {
            return '';
        }

        return $icon;
    }

    public static function create(string $icon, string $ariaLabel = ''): self
    {
        return new self($icon, $ariaLabel);
    }

    public static function decorative(string $icon): self
    {
        return new self($icon, '', role: 'presentation');
    }
}