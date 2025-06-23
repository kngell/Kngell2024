<?php

declare(strict_types=1);

class DefaultNavbarDecorator extends AbstractHtmlDecorator
{
    public function __construct(Controller $controller)
    {
        parent::__construct($controller);
    }

    public function page(): array
    {
        /** @var NavbarHtmlElement */
        $navElements = new NavbarHtmlElement(
            $this->builder,
            $this->session,
            $this->request
        );
        return ['navComponent' => $navElements->display()];
    }
}