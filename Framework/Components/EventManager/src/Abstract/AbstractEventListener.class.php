<?php

declare(strict_types=1);

abstract class AbstractEventListener implements ListenerProviderInterface
{
    public function checkEvent(string $name) : void
    {
        if (! $this->exists($name)) {
            throw new BaseInvalidArgumentException("No event has been registered under [$name] , please check your config!");
        }
    }

    public function listnerCanBeInstantiated(string $class) : EventListenerInterface
    {
        $object = App::diGet($class);
        if (! $object instanceof EventListenerInterface) {
            throw new BaseInvalidArgumentException("Listener can not be instantiate [$class]!");
        }
        return $object;
    }
}