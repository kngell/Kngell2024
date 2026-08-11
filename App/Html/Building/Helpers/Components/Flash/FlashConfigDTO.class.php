<?php

declare(strict_types=1);

/**
 * Container-level configuration for the FlashMessage component.
 * Holds rendering options that apply to ALL messages in the same container.
 *
 * Per-message data (type, message, duration, title, etc.) lives in FlashMessageDTO.
 */
final class FlashConfigDTO
{
    public const DEFAULT_ICON_MAP = [
        'success' => 'icon-check-circle',
        'danger' => 'icon-error',
        'warning' => 'icon-warning',
        'info' => 'icon-info',
    ];

    public const FALLBACK_ICON = 'icon-info';

    public function __construct(
        public readonly bool $useToast = false,
        public readonly array $iconMap = self::DEFAULT_ICON_MAP,
        public readonly string $fallbackIcon = self::FALLBACK_ICON,
    ) {
    }

    public function iconFor(string $type): string
    {
        return $this->iconMap[$type] ?? $this->fallbackIcon;
    }

    public static function default(): self
    {
        return new self();
    }

    public static function toast(): self
    {
        return new self(useToast: true);
    }
}