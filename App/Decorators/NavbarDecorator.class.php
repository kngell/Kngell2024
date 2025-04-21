<?php

declare(strict_types=1);

class NavbarDecorator extends AbstractHtmlDecorator
{
    public function __construct(Controller $controller)
    {
        parent::__construct($controller);
    }

    public function page(): array
    {
        /** @var NavbarHtmlElement */
        $navElements = new NavbarHtmlElement($this->controller->getBuilder(), $this->controller->getSession(), $this->controller->getRequest());
        return ['navComponent' => $navElements->display()];
    }
}
