<?php

declare(strict_types=1);

/**
 * @template V
 */
class Map implements IteratorAggregate, Countable
{
    /**
     * @var array<string|int,V>
     */
    protected array $items;

    /**
     * @param array<string|int,V> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * @param array<string|int,V> $array
     * @return void
     */
    public function addAll(array $array) : void
    {
        $this->items = array_merge($this->items, $array);
    }

    /**
     * @param string|int $key
     * @param V $value
     * @return void
     */
    public function add(string|int $key, mixed $value) : void
    {
        $this->items[$key] = $value;
    }

    public function has(string|int $key) : bool
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * @return array<string|int,V>
     */
    public function getAll() : array
    {
        return $this->items;
    }

    /**
     * @param string|int $key
     * @return mixed
     */
    public function get(string|int $key) : mixed
    {
        return $this->items[$key] ?? null;
    }

    public function remove(string|int $key) : void
    {
        if ($this->has($key)) {
            unset($this->items[$key]);
        }
    }

    public function clear() : void
    {
        $this->items = [];
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
