<?php

declare(strict_types=1);

class SaveHeroImageListener implements EventListenerInterface
{
    public function __construct(
    ) {
    }

    public function handle(EventInterface $event): ?object
    {
        return null;
    }
}