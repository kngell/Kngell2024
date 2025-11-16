<?php

declare(strict_types=1);
class ListenerProvider extends AbstractEventListener
{
    public function __construct(private array $listeners = [], private array $log = [])
    {
    }

    public function getListenersForEvent(EventInterface $event): iterable
    {
        $eventName = $event->getName();

        if (array_key_exists($eventName, $this->listeners)) {
            return $this->listeners[$eventName];
        }
        return [];
    }

    public function add(string $name, EventListenerInterface $listener, int $priority = 0): void
    {
        if (!isset($this->listeners[$name])) {
            $this->listeners[$name] = [];
        }
        $this->listeners[$name][] = ['callback' => $listener::class, 'priority' => $priority];
    }

    /** @inheritDoc */
    public function exists(string $name): bool
    {
        return array_key_exists($name, $this->listeners);
    }

    /** @inheritDoc */
    public function hasListener(string $event, string $listener): bool
    {
        return isset($this->listeners[$event]) ? in_array($listener, $this->listeners[$event]) : false;
    }

    /** @inheritDoc */
    public function removeAll(string $name): void
    {
        $this->checkEvent(name:$name);
        unset($this->listeners[$name]);
    }

    public function remove(EventInterface $event, string $listener): void
    {
        $eventName = $event::class;
        $this->checkEvent(name: $eventName);

        $listeners = $this->listeners[$eventName];

        foreach ($listeners as $key => $item) {
            if ($item['callback'] === $listener) {
                unset($this->listeners[$eventName][$key]);

                return;
            }
        }
        throw new BaseInvalidArgumentException("Listener has not been registered for [{$eventName}]", 1);
    }

    public function detach(EventInterface $event, callable $callback): void
    {
        $this->listeners[$event->getName()] = array_filter($this->listeners[$event->getName()], function ($listener) use ($callback) {
            return $listener['callback'] !== $callback;
        });
    }

    /**
     * @param string $eventType
     */
    public function clearListeners(string $eventType): void
    {
        if (array_key_exists($eventType, $this->listeners)) {
            unset($this->listeners[$eventType]);
        }
    }

    /** @inheritDoc */
    public function append(string $name, array $listeners): void
    {
        $this->checkEvent(name:$name);
        foreach ($listeners as $listener) {
            array_push($this->listeners[$name], $listener);
        }
    }

    /** @inheritDoc */
    public function listeners(): array
    {
        return $this->listeners;
    }

    /** @inheritDoc */
    public function log(): array
    {
        return $this->log;
    }
}