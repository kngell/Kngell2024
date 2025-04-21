<?php

declare(strict_types=1);

abstract class AbstractHtmlDecorator extends Controller
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
    }
}