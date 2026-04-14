<?php

declare(strict_types=1);

interface MenuItemComponentInterface
{
    public function getRegularItem(NavigationItem $item, array $liClasses, array $linkClasses): AbstractHtmlComponent;

    public function getDropdownItem(NavigationItem $item): AbstractHtmlComponent;
}