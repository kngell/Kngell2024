<?php

declare(strict_types=1);

class NavigationItem
{
    public function __construct(
        public readonly string $name,
        public readonly string $link,
        public readonly string $type = 'regular',
        public readonly ?NavigationIcon $icon = null,
        public readonly ?NavigationIcon $iconLeft = null,
        public readonly ?NavigationIcon $iconRight = null,
        public readonly array $dropdownItems = [],
        public readonly array $attributes = [],
    ) {
    }
}

class NavigationIcon
{
    public function __construct(
        public readonly string $name,
        public readonly string $aria,
        public readonly array $classes = [],
    ) {
    }
}