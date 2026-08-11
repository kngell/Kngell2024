<?php

declare(strict_types=1);

final class EventDispatcher implements EventDispatcherInterface
{
    private array $log = [];

    public function __construct(
        private readonly ListenerProviderInterface $provider,
        private readonly ListenerResolverInterface $resolver,  // ✅ readonly
    ) {
    }

    public function notify(EventInterface $event, bool $debug = false): EventInterface
    {
        $this->provider->assertEventExists($event->getName());

        foreach ($this->provider->getListenersForEvent($event) as ['callback' => $listenerClass]) {
            if ($event->isPropagationStopped()) {
                break;
            }

            $listener = $this->resolver->resolve($listenerClass);
            $result = $listener->handle($event);

            $event->addResult($listenerClass, $result);

            if ($debug) {
                $this->log[$event->getName()][] = $result;
            }
        }

        return $event;
    }

    public function subscribe(string $eventName, EventListenerInterface $listener, int $priority = 0): static
    {
        $this->provider->add($eventName, $listener, $priority);
        return $this;
    }

    public function unsubscribe(EventInterface $event, string $listenerClass): void
    {
        $this->provider->remove($event, $listenerClass);
    }

    public function getLog(): array
    {
        return $this->log;
    }
}