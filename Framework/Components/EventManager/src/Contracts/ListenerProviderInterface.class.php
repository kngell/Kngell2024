<?php

declare(strict_types=1);
interface ListenerProviderInterface
{
    /**
     * Get Listener for event
     * -------------------------------------------------------.
     * @param EventInterface $event
     * An event for which to return relevant listeners
     * @return iterable
     */
    public function getListenersForEvent(EventInterface $event) : iterable;

    /**
     * Add an event with its Dispatcher
     * ---------------------------------------------------------------.
     * @param string $eventName
     * @param EventListenerInterface $listener
     * @return void
     */
    public function add(string $eventName, EventListenerInterface $listener) : void;

    /**
     * Append a Series of listeners into the events listeners array.
     * --------------------------------------------------------------.
     * @param string $name
     * @param array $listenerss
     * @return void
     */
    public function append(string $name, array $listenerss) : void;

    /**
     * Check if the passed in event name has been registered.
     * ------------------------------------------------------------.
     * @param string $name
     * @return bool
     */
    public function exists(string $name) : bool;

    /**
     * Check if the passed in listner has been registered for the passed in Event
     * ----------------------------------------------------------.
     * @param string $event
     * @param string $listener
     * @return bool
     */
    public function hasListener(string $event, string $listener) : bool;

    /**
     * Remove all listeners and the event for the passed Event.
     * -------------------------------------------------------------.
     * @param string $name
     * @return void
     */
    public function removeAll(string $name) :void;

    /**
     * Remove a specific listeners from the events array for an event.
     * --------------------------------------------------------------.
     * @param EventInterface $event
     * @param string $listeners
     * @return void
     */
    public function remove(EventInterface $event, string $listener) : void;

    public function checkEvent(string $name) : void;

    public function listnerCanBeInstantiated(string $class) : EventListenerInterface;

    public function log() : array;
}