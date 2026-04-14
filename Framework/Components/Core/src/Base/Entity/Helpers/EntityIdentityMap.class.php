<?php

declare(strict_types=1);
class EntityIdentityMap
{
    private array $map = [];

    /**
     * @param string $className The FQCN
     * @param mixed $id The Primary Key value
     */
    public function get(string $className, mixed $id): ?Entity
    {
        return $this->map[$className][$id] ?? null;
    }

    public function set(string $className, mixed $id, Entity $entity): void
    {
        $this->map[$className][$id] = $entity;
    }

    public function clear(): void
    {
        $this->map = [];
    }
}
