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
        $navElements = App::diget(NavbarHtmlElement::class);
        return ['navComponent' => $navElements->display()];
    }
}