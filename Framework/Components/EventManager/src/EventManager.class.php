<?php

declare(strict_types=1);

class EventManager extends AbstractEventManager
{
    public function __construct(private ListenerProviderInterface $provider)
    {
    }

    public function notify(string|EventInterface $event, ?Object $object, bool $debug = false): ?object
    {
        $eventResults = [];

        $event = $this->getEvent($event, $object);

        $this->provider->checkEvent(name: $event->getName());

        $listeners = $this->getListenersForEvent(event: $event);
        foreach ($listeners as ['callback' => $listener]) {
            /** @var EventListenerInterface */
            $listenerObj = $this->provider->listnerCanBeInstantiated(class: $listener);
            $eventResults = $listenerObj->handle(event: $event);
            $event->addResult($listener, $eventResults);
            if ($debug) {
                $this->provider->log()[$event->getName()][] = $eventResults;
            }
        }
        return $event;
    }

    public function add(string $eventName, EventListenerInterface $listener, int $priority = 0): self
    {
        $this->provider->add($eventName, $listener, $priority);
        return $this;
    }

    public function remove(EventInterface $event, string $listener): void
    {
        $this->provider->remove($event, $listener);
    }

    private function getListenersForEvent(EventInterface $event): iterable
    {
        /** @var array */
        $listeners = $this->provider->getListenersForEvent(event: $event);
        if ($listeners === null) {
            return [];
        }
        uasort($listeners, function ($listenerA, $listenerB) {
            return $listenerB['priority'] - $listenerA['priority'];
        });
        return $listeners;
    }
}