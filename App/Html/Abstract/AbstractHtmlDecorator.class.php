<?php

declare(strict_types=1);

abstract class AbstractHtmlDecorator extends Controller implements HtmlDecoratorInterface
{
    public function __construct(protected Controller $controller)
    {
    }

    public function __get(string $name): mixed
    {
        return $this->controller->$name;
    }

    public function __call(string $name, mixed $arguments): void
    {
        $this->controller->__call($name, $arguments);
    }

    public function getTarget(): Controller
    {
        $current = $this->controller;

        while ($current instanceof HtmlDecoratorInterface) {
            $current = $current->getTarget();
        }

        return $current;
    }

    public function page(): array
    {
        return $this->controller->page();
    }
}