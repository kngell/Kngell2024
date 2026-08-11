<?php

declare(strict_types=1);

class AdminNavbarDecorator extends AbstractHtmlDecorator
{
    public function __construct(private AdminNavSectionProvider $provider, AbstractHtmlDecorator|Controller|null $controller = null)
    {
        parent::__construct($controller);
    }

    public function page(): array
    {
        $target = $this->getTarget();

        $navElement = new AdminNavbar(
            $this->provider,
            $target->getSectionManager(),
            $target->getBuilder(),
        );

        $sideBarMenuList = $navElement->getHtmlElements();
        return parent::page() + ['menulist' => $sideBarMenuList];
    }
}