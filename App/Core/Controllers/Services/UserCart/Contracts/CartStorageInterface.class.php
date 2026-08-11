<?php

declare(strict_types=1);

interface CartStorageInterface
{
    public function load(): CartCollection;

    public function save(CartCollection $cart): void;

    public function clear(): void;
}