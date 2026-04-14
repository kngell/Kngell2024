<?php

declare(strict_types=1);

class LazyTagCollection implements IteratorAggregate, Countable
{
    private array $resolved = [];
    private array $unresolved = [];
    private ContainerInterface $container;
    private string $tag;

    public function __construct(Container $container, string $tag, array $serviceIds)
    {
        $this->container = $container;
        $this->tag = $tag;
        $this->unresolved = $serviceIds;
    }

    public function getIterator(): Traversable
    {
        foreach ($this->unresolved as $id) {
            yield $this->get($id);
        }

        foreach ($this->resolved as $service) {
            yield $service;
        }
    }

    public function all(): array
    {
        $result = [];
        foreach ($this as $service) {
            $result[] = $service;
        }
        return $result;
    }

    public function first(): mixed
    {
        foreach ($this as $service) {
            return $service;
        }
        return null;
    }

    public function count(): int
    {
        return count($this->unresolved) + count($this->resolved);
    }

    private function get(string $id): mixed
    {
        if (!isset($this->resolved[$id])) {
            $this->resolved[$id] = $this->container->get($id);
            unset($this->unresolved[array_search($id, $this->unresolved)]);
        }
        return $this->resolved[$id];
    }
}