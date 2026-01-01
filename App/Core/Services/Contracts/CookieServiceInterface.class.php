<?php

declare(strict_types=1);
interface CookieServiceInterface
{
    public function get(string $name): mixed;

    public function set(string $name, mixed $value, array $options = []): void;

    public function delete(string $name): void;

    public function has(string $name): bool;

    public function getAll(): array;
}