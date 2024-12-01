<?php

declare(strict_types=1);

class WebElementDecorator implements ControllerInterface
{
    protected ControllerInterface $controller;

    public function __construct(ControllerInterface $controller)
    {
        $this->controller = $controller;
    }

    public function page(): string
    {
        return $this->controller->page();
    }
}
