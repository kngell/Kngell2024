<?php

declare(strict_types=1);

class DecoratorFactory
{
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    /**
     * @template T of AbstractHtmlDecorator
     *
     * @param class-string<T>                   $decoratorClass
     * @param AbstractHtmlDecorator|Controller   $target
     * @param array<string, mixed>               $params  Runtime parameters (ignored if decorator is not configurable)
     *
     * @return T
     */
    public function create(
        string $decoratorClass,
        AbstractHtmlDecorator|Controller $target,
        array|object $params = [],
    ): AbstractHtmlDecorator {
        $decorator = $this->container->get($decoratorClass);

        if (!$decorator instanceof AbstractHtmlDecorator) {
            throw new InvalidArgumentException(
                sprintf('%s must extend AbstractHtmlDecorator.', $decoratorClass),
            );
        }

        $decorator->target($target);

        if ($decorator instanceof RuntimeConfigurableInterface && !empty($params)) {
            $decorator->configure($params);
        }

        return $decorator;
    }
}