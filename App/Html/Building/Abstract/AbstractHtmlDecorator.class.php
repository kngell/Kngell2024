<?php

declare(strict_types=1);
abstract class AbstractHtmlDecorator extends Controller implements HtmlDecoratorInterface, RuntimeConfigurableInterface
{
    use RuntimeConfigurableTrait;

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

    public function __call(string $name, array $arguments): mixed
    {
        return $this->controller->{$name}(...$arguments);
    }

    public function getTarget(): AbstractHtmlDecorator|Controller
    {
        $current = $this->controller;
        while ($current instanceof HtmlDecoratorInterface) {
            $current = $current->getTarget();
        }
        return $current;
    }

    public function target(AbstractHtmlDecorator|Controller $target): void
    {
        $this->controller = $target;
    }

    public function page(): array
    {
        return $this->controller->page();
    }
}