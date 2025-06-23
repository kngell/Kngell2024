<?php

declare(strict_types=1);

abstract class AbstractEventManager implements EventManagerInterface
{
    protected function getEvent(string|EventInterface $event, ?object $obj = null) : EventInterface
    {
        if (is_string($event)) {
            /** @var Event */
            $eventObj = new $event($obj);
            if (! $eventObj instanceof EventInterface) {
                throw new BadEnventManagerException("[$eventObj::class] is not a valid Event Object!", 1);
            }
            return $eventObj;
        }
        return $this->event($event, $obj);
    }

    private function event(EventInterface $event, ?object $obj = null) : ?EventInterface
    {
        if ($event->getObject() === null) {
            $event->setObject($obj);
        }
        if ($event->getName() === '') {
            $event->setName($obj::class);
        }
        return $event;
    }
}
