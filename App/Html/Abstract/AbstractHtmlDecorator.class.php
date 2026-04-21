<?php

declare(strict_types=1);

abstract class AbstractHtmlDecorator extends Controller implements HtmlDecoratorInterface
{
    protected AbstractHtmlDecorator|Controller $controller;

    public function __construct(AbstractHtmlDecorator|Controller|null $controller = null)
    {
        if ($controller !== null) {
            $this->controller = $controller;
        }
    }

    public function __get(string $name): mixed
    {
        return $this->controller->$name;
    }

    public function __call(string $name, mixed $arguments): void
    {
        $this->controller->__call($name, $arguments);
    }

    /**
     * Traverses the decorator chain to find the original controller.
     */
    public function getTarget(): AbstractHtmlDecorator|Controller
    {
        $current = $this->controller;

        while ($current instanceof HtmlDecoratorInterface) {
            $current = $current->getTarget();
        }

        return $current;
    }

    /**
     * Sets the wrapped target (controller or another decorator).
     */
    public function target(AbstractHtmlDecorator|Controller $target): void
    {
        $this->controller = $target;
    }

    /**
     * Delegates to the wrapped target's page().
     * Concrete decorators call parent::page() to collect
     * accumulated page data from the chain, then merge their own.
     */
    public function page(): array
    {
        return $this->controller->page();
    }
}