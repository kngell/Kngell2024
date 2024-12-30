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
        return array_merge(
            ['navComponent' => $navElements->display()],
            $this->controller->page()
        );
    }
}