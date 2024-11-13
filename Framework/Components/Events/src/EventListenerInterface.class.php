<?php

declare(strict_types=1);

interface EventListenerInterface
{
    public function listenEvent(Event $event) : void;

    public function supports(string $eventId) : bool;
}