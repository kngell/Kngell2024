<?php

declare(strict_types=1);

final class ListenerProvider implements ListenerProviderInterface
{
    public function __construct(private array $listeners = [])
    {
    }

    public function getListenersForEvent(EventInterface $event): iterable
    {
        $eventName = $event->getName();

        if (!array_key_exists($eventName, $this->listeners)) {
            return [];
        }

        $listeners = $this->listeners[$eventName];

        // ✅ Warn on registered but empty event
        if ($listeners === []) {
            trigger_error(
                "Event [$eventName] is registered but has no active listeners.",
                E_USER_NOTICE,
            );
            return [];
        }

        uasort($listeners, static fn (array $a, array $b) => $b['priority'] - $a['priority']);

        return $listeners;
    }

    public function add(string $eventName, EventListenerInterface $listener, int $priority = 0): void
    {
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        $this->listeners[$eventName][] = [
            'callback' => $listener::class,
            'priority' => $priority,
        ];
    }

    public function assertEventExists(string $eventName): void
    {
        if (!$this->exists($eventName)) {
            return;
            // throw new EventNotFoundException(
            //     "No event has been registered under [$eventName]. Please check your configuration.",
            // );
        }
    }

    public function append(string $eventName, array $listeners): void
    {
        $this->assertEventExists($eventName);

        foreach ($listeners as $listener) {
            $this->listeners[$eventName][] = $listener;
        }
    }

    public function removeAll(string $eventName): void
    {
        $this->assertEventExists($eventName);
        unset($this->listeners[$eventName]);
    }

    public function remove(EventInterface $event, string $listenerClass): void
    {
        $eventName = $event->getName();
        $this->assertEventExists($eventName);

        foreach ($this->listeners[$eventName] as $key => $item) {
            if ($item['callback'] === $listenerClass) {
                unset($this->listeners[$eventName][$key]);
                return;
            }
        }

        throw new InvalidListenerException(
            "Listener [$listenerClass] has not been registered for event [$eventName].",
        );
    }

    public function exists(string $eventName): bool
    {
        return array_key_exists($eventName, $this->listeners);
    }

    public function hasListener(string $eventName, string $listenerClass): bool
    {
        if (!isset($this->listeners[$eventName])) {
            return false;
        }

        // ✅ Fixed: was comparing string against full array item
        foreach ($this->listeners[$eventName] as $item) {
            if ($item['callback'] === $listenerClass) {
                return true;
            }
        }

        return false;
    }
}