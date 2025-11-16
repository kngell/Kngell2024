<?php

declare(strict_types=1);

abstract class AbstractEventManager implements EventManagerInterface
{
    protected function getEvent(string|EventInterface $event, ?object $obj = null): EventInterface
    {
        if (is_string($event)) {
            if (class_exists($event)) {
                $eventClassName = $event;
                return new $event($eventClassName, $obj, []);
            }
            throw new BadEnventManagerException(
                "When notifying, you must pass the fully constructed Event Object (e.g., new ProductGalleryEvent('product.created', ...)), not just the class name.",
                1,
            );
        }

        if (empty($event->getName())) {
            throw new BadEnventManagerException('Event object must be constructed with a non-empty stable string name.', 1);
        }
        return $event;
    }

    // protected function getEvent(string|EventInterface $event, ?object $obj = null): EventInterface
    // {
    //     if (is_string($event)) {
    //         /** @var Event */
    //         $eventObj = new $event($obj);
    //         if (!$eventObj instanceof EventInterface) {
    //             throw new BadEnventManagerException("[$eventObj::class] is not a valid Event Object!", 1);
    //         }
    //         return $eventObj;
    //     }
    //     return $this->event($event, $obj);
    // }

    private function event(EventInterface $event, ?object $obj = null): ?EventInterface
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