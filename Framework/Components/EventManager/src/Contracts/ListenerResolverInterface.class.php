<?php

declare(strict_types=1);

interface ListenerResolverInterface
{
    /**
     * Resolve a listener FQCN to a concrete EventListenerInterface instance.
     *
     * @throws InvalidListenerException
     */
    public function resolve(string $class): EventListenerInterface;
}