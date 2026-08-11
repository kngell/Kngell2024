<?php

declare(strict_types=1);

interface ListenerProviderInterface
{
    /**
     * Return listeners registered for the given event, sorted by priority.
     */
    public function getListenersForEvent(EventInterface $event): iterable;

    /**
     * Register a listener class for a named event.
     */
    public function add(string $eventName, EventListenerInterface $listener, int $priority = 0): void;

    /**
     * Append multiple pre-built listener definitions to an event.
     *
     * Each entry must be: ['callback' => FQCN, 'priority' => int]
     */
    public function append(string $eventName, array $listeners): void;

    /**
     * Remove all listeners for the given event name.
     */
    public function removeAll(string $eventName): void;

    /**
     * Remove a specific listener class from an event.
     */
    public function remove(EventInterface $event, string $listenerClass): void;

    /**
     * Check whether an event name has been registered.
     */
    public function exists(string $eventName): bool;

    /**
     * Check whether a specific listener is registered for an event.
     */
    public function hasListener(string $eventName, string $listenerClass): bool;

    /**
     * Assert an event exists, throwing if it does not.
     */
    public function assertEventExists(string $eventName): void;
}