<?php

declare(strict_types=1);

interface MenuItemComponentInterface
{
    public function getRegularItem(NavigationItem $item, array $liClasses, array $linkClasses): AbstractHtmlComponent;

    public function getDropdownItem(NavigationItem $item): array|AbstractHtmlComponent;

    public function buildMenuItems(array $navigationItems): array;

    public function loadNavigationItems(string $menuPath): array;
}