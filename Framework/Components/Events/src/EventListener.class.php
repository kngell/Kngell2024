<?php

declare(strict_types=1);

class EventListener
{
    /**
     * @var EventListenerInterface[]
     */
    private array $listeners = [];

    public function add(string $listenerId, EventListenerInterface $listener) : void
    {
        $this->listeners[$listenerId] = $listener;
    }

    public function remove(string $listenerId) : void
    {
        if (array_key_exists($listenerId, $this->listeners)) {
            unset($this->listeners[$listenerId]);
        }
    }

    public function sendEvent(Event $event) : void
    {
        foreach ($this->listeners as $listener) {
            if ($listener->supports($event->getId())) {
                $listener->listenEvent($event);
            }
        }
    }
}