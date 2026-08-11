<?php

declare(strict_types=1);
interface EntityResponseInterface extends DTOResponseInterface
{
    public function getEntity(): ?Entity;

    public function hasEntity(): bool;
}