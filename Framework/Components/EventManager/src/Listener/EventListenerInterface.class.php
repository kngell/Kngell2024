<?php

declare(strict_types=1);

interface EventListenerInterface
{
    public function update(EventInterface $event) : ?object;
}