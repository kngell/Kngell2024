<?php

declare(strict_types=1);

final class ListenerResolver implements ListenerResolverInterface
{
    /**
     * @param ContainerInterface $container PSR-11 container
     */
    public function __construct(private readonly ContainerInterface $container)
    {
    }

    public function resolve(string $class): EventListenerInterface
    {
        if (!$this->container->has($class)) {
            throw new InvalidListenerException(
                "No binding found in container for listener [$class].",
            );
        }

        $object = $this->container->get($class);

        if (!$object instanceof EventListenerInterface) {
            throw new InvalidListenerException(
                "Class [$class] was resolved but does not implement EventListenerInterface.",
            );
        }

        return $object;
    }
}