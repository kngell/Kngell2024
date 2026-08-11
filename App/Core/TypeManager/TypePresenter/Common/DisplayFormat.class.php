<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_PROPERTY)]
class DisplayFormat
{
    // Global obfuscation constants
    public const OBFUSCATION_PREFIX = '#';
    public const OBFUSCATION_PREFIXES = ['#', 'enc:', 'obf:', 'hash:'];
    public const DEFAULT_OBFUSCATION_STRATEGY = 'hashid';

    public function __construct(
        // General display style
        public ?string $style = null, // 'auto', 'date', 'time', 'datetime', 'relative', 'yesno', 'truefalse', 'activeinactive', 'onoff'

        // Custom format strings (PHP date() format)
        public ?string $format = null,

        // Style names or format strings for date/time
        public ?string $dateStyle = null, // e.g., 'Y-m-d' or 'short', 'medium'
        public ?string $timeStyle = null, // e.g., 'H:i:s' or 'short', 'medium'

        // For numbers
        public ?int $decimals = null,
        public ?string $numberStyle = null,

        // For arrays/collections
        public ?string $separator = null,
        public ?int $maxItems = null,

        // For units/measurements
        public ?bool $showUnit = null,
        public ?string $unit = null,
        public ?bool $compact = null,

        // Obfuscation support
        public ?bool $obfuscate = null,
        public ?string $obfuscationStrategy = null,

        // General
        public ?string $prefix = null,
        public ?string $suffix = null,
        public ?string $nullPlaceholder = null,

        // NEW: Raw value flag - when true, returns the raw value instead of formatted
        public ?bool $raw = null,
    ) {
        if ($this->obfuscate === true && $this->prefix === null) {
            $this->prefix = self::OBFUSCATION_PREFIX;
        }

        // Auto-set default strategy if not specified
        if ($this->obfuscate === true && $this->obfuscationStrategy === null) {
            $this->obfuscationStrategy = self::DEFAULT_OBFUSCATION_STRATEGY;
        }
    }
}