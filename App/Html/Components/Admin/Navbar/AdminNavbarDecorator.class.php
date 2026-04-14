<?php

declare(strict_types=1);

class AdminNavbarDecorator extends AbstractHtmlDecorator
{
    public function page(): array
    {
        $target = $this->getTarget();

        $navElement = new AdminNavbar(
            $target->getProviderFactory(),
            $target->getSectionManager(),
            $target->getBuilder(),
        );

        [$sideBarMenuList] = $navElement->getHtmlElements();
        return parent::page() + ['menulist' => $sideBarMenuList];
    }
}