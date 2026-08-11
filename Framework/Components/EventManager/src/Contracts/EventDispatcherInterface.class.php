<?php

declare(strict_types=1);

interface EventDispatcherInterface
{
    /**
     * Dispatch an event to all registered listeners.
     */
    public function notify(EventInterface $event, bool $debug = false): EventInterface;

    /**
     * Subscribe a listener to a named event.
     */
    public function subscribe(string $eventName, EventListenerInterface $listener, int $priority = 0): static;

    /**
     * Unsubscribe a specific listener class from an event.
     */
    public function unsubscribe(EventInterface $event, string $listenerClass): void;
}