<?php

// DisplayFormat.php
declare(strict_types=1);

#[Attribute(Attribute::TARGET_PROPERTY)]
class DisplayFormat
{
    public function __construct(
        // General display style
        public ?string $style = null, // 'auto', 'date', 'time', 'datetime', 'relative'

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

        // NEW: Obfuscation support
        public ?bool $obfuscate = null,      // Whether to obfuscate IDs
        public ?string $obfuscationStrategy = null, // 'hashid', 'encrypt', or null for default

        // General
        public ?string $prefix = null,
        public ?string $suffix = null,
        public ?string $nullPlaceholder = null,
    ) {
    }
}