<?php

declare(strict_types=1);

interface EventManagerInterface
{
    public function notify(string|EventInterface $event, ?Object $object, bool $debug = false) : ?object;

    public function add(string $name, EventListenerInterface $listener, int $priority = 0) : self;

    public function remove(EventInterface $event, string $listener) : void;
}
