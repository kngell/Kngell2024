<?php

declare(strict_types=1);

class EventManager extends AbstractEventManager
{
    public function __construct(private ListenerProviderInterface $listeners)
    {
    }

    public function notify(string|EventInterface $event, ?Object $object, bool $debug = false) : ?object
    {
        $eventResults = [];
        $event = $this->getEvent($event, $object);
        $this->listeners->checkEvent(name: $event->getName());
        $listeners = $this->getListenersForEvent(event: $event);
        foreach ($listeners as ['callback' => $listener]) {
            /** @var EventListenerInterface */
            $listenerObj = $this->listeners->listnerCanBeInstantiated(class: $listener);
            $eventResults[] = $listenerObj->update(event: $event);
            if ($debug) {
                $this->listeners->log()[$event->getName()][] = $eventResults;
            }
        }
        return $event;
    }

    public function add(string $eventName, EventListenerInterface $listener, int $priority = 0) : self
    {
        $this->listeners->add($eventName, $listener, $priority);
        return $this;
    }

    public function remove(EventInterface $event, string $listener) : void
    {
        $this->listeners->remove($event, $listener);
    }

    private function getListenersForEvent(EventInterface $event) : iterable
    {
        /** @var array */
        $listeners = $this->listeners->getListenersForEvent(event: $event);
        uasort($listeners, function ($listenerA, $listenerB) {
            return $listenerB['priority'] - $listenerA['priority'];
        });
        return $listeners;
    }
}
