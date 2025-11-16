<?php

declare(strict_types=1);

interface DataDefinitionInterface
{
    public function create();

    public function alter();

    public function drop();
}