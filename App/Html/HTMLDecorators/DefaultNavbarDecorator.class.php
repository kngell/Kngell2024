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
        $target = $this->getTarget();
        /** @var NavbarHtmlElement */
        $navElements = new NavbarHtmlElement(
            $target->builder,
            $target->session,
            $target->request,
        );
        return ['navComponent' => $navElements->display()];
    }
}