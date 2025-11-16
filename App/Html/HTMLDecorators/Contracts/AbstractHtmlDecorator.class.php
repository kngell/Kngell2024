<?php

declare(strict_types=1);

abstract class AbstractHtmlDecorator extends Controller
{
    protected Controller $controller;

    public function __construct(Controller $controller)
    {
        $this->controller = $controller;
        $this->setProperties();
    }

    private function setProperties() : void
    {
        foreach ($this->controller as $key => $value) {
            if (property_exists($this, $key) && $key !== 'controller') {
                $this->$key = $value;
            }
        }
    }
}