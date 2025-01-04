<?php

declare(strict_types=1);

class NullEvent extends Event implements EventInterface
{
    public function getName(): string
    {
        return 'null-event';
    }
}