<?php

declare(strict_types=1);

class NavigationConfigParser
{
    private const array VALID_TYPES = ['regular', 'dropdown'];
    private const array REQUIRED_REGULAR_FIELDS = ['link', 'icon', 'type'];
    private const array REQUIRED_DROPDOWN_FIELDS = ['type', 'icon-left', 'icon-right', 'dropdown-items'];

    public function parse(array $config): array
    {
        $items = [];

        foreach ($config as $name => $itemConfig) {
            $this->validateItemConfig($name, $itemConfig);
            $items[] = $this->createNavigationItem($name, $itemConfig);
        }

        return $items;
    }

    private function validateItemConfig(string $name, array $config): void
    {
        if (!isset($config['type'])) {
            throw new InvalidArgumentException(
                "Navigation item '{$name}' must have a type",
            );
        }

        if (!in_array($config['type'], self::VALID_TYPES)) {
            throw new InvalidArgumentException(
                "Navigation item '{$name}' has invalid type '{$config['type']}'. Valid types: " .
                implode(', ', self::VALID_TYPES),
            );
        }

        if ($config['type'] === 'regular') {
            $this->validateRegularItem($name, $config);
        } else {
            $this->validateDropdownItem($name, $config);
        }
    }

    private function validateRegularItem(string $name, array $config): void
    {
        foreach (self::REQUIRED_REGULAR_FIELDS as $field) {
            if (!isset($config[$field])) {
                throw new InvalidArgumentException(
                    "Regular navigation item '{$name}' is missing required field: '{$field}'",
                );
            }
        }
    }

    private function validateDropdownItem(string $name, array $config): void
    {
        foreach (self::REQUIRED_DROPDOWN_FIELDS as $field) {
            if (!isset($config[$field])) {
                throw new InvalidArgumentException(
                    "Dropdown navigation item '{$name}' is missing required field: '{$field}'",
                );
            }
        }

        // if (!is_array($config['dropdown-items']) || empty($config['dropdown-items'])) {
        //     throw new InvalidArgumentException(
        //         "Dropdown navigation item '{$name}' must have non-empty dropdown-items array",
        //     );
        // }
    }

    private function createNavigationItem(string $name, array $config): NavigationItem
    {
        $type = $config['type'];

        $icon = isset($config['icon'])
            ? $this->createIcon($config['icon'])
            : null;

        $iconLeft = isset($config['icon-left'])
            ? $this->createIcon($config['icon-left'])
            : null;

        $iconRight = isset($config['icon-right'])
            ? $this->createIcon($config['icon-right'])
            : null;

        $dropdownItems = [];
        if ($type === 'dropdown' && isset($config['dropdown-items'])) {
            $dropdownItems = $config['dropdown-items'];
        }

        return new NavigationItem(
            name: $name,
            link: $config['link'] ?? '',
            type: $type,
            icon: $icon,
            iconLeft: $iconLeft,
            iconRight: $iconRight,
            dropdownItems: $dropdownItems,
        );
    }

    private function createIcon($iconConfig): NavigationIcon
    {
        if (is_string($iconConfig)) {
            return new NavigationIcon(
                name: $iconConfig,
                aria: $this->generateAriaLabel($iconConfig),
                classes: [],
            );
        }

        return new NavigationIcon(
            name: $iconConfig['name'] ?? '',
            aria: $iconConfig['aria'] ?? $this->generateAriaLabel($iconConfig['name'] ?? ''),
            classes: $iconConfig['class'] ?? [],
        );
    }

    private function generateAriaLabel(string $iconName): string
    {
        $label = str_replace(['icon-', '-'], ['', ' '], $iconName);
        return ucfirst($label) . ' Icon';
    }
}