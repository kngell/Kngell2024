<?php

declare(strict_types=1);

interface MenuItemFactoryInterface
{
    public function createRegularItem(NavigationItem $item, array $liClasses, array $linkClasses): AbstractHtmlComponent;

    public function createDropdownItem(NavigationItem $item): AbstractHtmlComponent;
}