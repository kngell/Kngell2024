<?php

declare(strict_types=1);

class AdminNavbarDecorator extends AbstractHtmlDecorator
{
    public function __construct(Controller $controller)
    {
        parent::__construct($controller);
    }

    public function page(): array
    {
        /** @var AdminNavbarHtmlElement */
        $navElements = new AdminNavbarHtmlElement(
            $this->builder,
            $this->session,
            $this->request
        );
        return ['navComponent' => $navElements->display()];
    }
}