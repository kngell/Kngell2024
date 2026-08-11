<?php

declare(strict_types=1);

interface EventListenerInterface
{
    public function handle(EventInterface $event): mixed;
}