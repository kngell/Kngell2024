<?php

declare(strict_types=1);

class Collection implements CollectionInterface
{
    use CollectionTrait;

    /** @var array - collection items */
    protected mixed $items = [];

    public function __construct(mixed $items = [])
    {
        $this->items = (array) $items;
    }

    public function isEmpty() : bool
    {
        return $this->count === 0;
    }

    public function addAll(array $parameters) : self
    {
        foreach ($parameters as $key => $value) {
            $this->items[$key] = $value;
        }
        return $this;
    }

    /**
     * Add an item to the collection.
     *
     * @param mixed $item
     * @return self
     */
    public function add(mixed $item): self
    {
        $this->items[] = $item;
        return $this;
    }

    /**
     * Returns all the items within the collection.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->items;
    }

    public function contains(string $key)
    {
        return in_array($key, $this->items);
    }

    /**
     * Checks whether a given key exists within the collection.
     *
     * @param mixed $key
     * @return bool
     */
    public function has(mixed $key): bool
    {
        return array_key_exists($key, $this->items) && isset($this->items[$key]);
    }

    public function getObjectWithValue(string $id, mixed $value) : array
    {
        return array_values(
            array_filter($this->items, fn ($item) => $item->$id === $value)
        );
    }

    /**
     * Returns all the keys of the collection items.
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->items);
    }

    public function shuffle() : static
    {
        return new static(shuffle($this->items));
    }

    /**
     * Run a map over each items.
     *
     * @param callable $callback
     * @return static
     */
    public function map(callable $callback): static
    {
        $items = array_map($callback, $this->items, $this->keys());

        return new static(array_combine($this->keys(), $items));
    }

    public function avg()
    {
        if ($size = $this->size()) {
            $array = array_filter($this->items);
            return array_sum($array) / $size;
        }
    }

    /**
     * Calculates the sum of values within the specified array.
     *
     * @param array $array
     * @return static
     */
    public function sum(): static
    {
        return new static(array_sum($this->items));
    }

    public function min()
    {
    }

    public function max()
    {
    }

    /**
     * Create an collection with the specified ranges.
     *
     * @param mixed $from
     * @param mixed $to
     * @return static
     */
    public function range($from, $to): static
    {
        return new static(range($from, $to));
    }

    /**
     * Merge the collection with the given argument.
     *
     * @param mixed $items
     * @return static
     */
    public function merge(mixed $items): static
    {
        return new static(array_merge($this->items, $items));
    }

    /**
     * Recursively merge the collection with the given argument.
     *
     * @param mixed $items
     * @return static
     */
    public function mergeRecursive(mixed $items): static
    {
        return new static(array_merge_recursive($this->items, $items));
    }

    /**
     * Pop an element off the end of the item collection.
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
    }

    /**
     * Push elements on the end of the collection items.
     *
     * @param mixed ...$values
     * @return self
     */
    public function push(...$values): self
    {
        array_push($this->items, $values);

        return $this;
    }

    /**
     * Returns the items collection in reverse order.
     *
     * @return static
     */
    public function reverse(): static
    {
        return new static(array_reverse($this->items, true));
    }

    /**
     * Shift the first element of the collection items.
     *
     * @return mixed
     */
    public function shift(): mixed
    {
        return array_shift($this->items);
    }

    /**
     * Extract a slice of the collection items.
     *
     * @param [type] $offset
     * @param [type] $length
     * @return static
     */
    public function slice(int $offset, $length = null): static
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }

    /**
     * Returns the values of the collection items.
     *
     * @return static
     */
    public function values(): static
    {
        return new static(array_values($this->items));
    }

    /**
     * Count the number of items within the collection items.
     *
     * @return int
     */
    public function size(): int
    {
        return count($this->items);
    }

    /**
     * Remove the item from the collection.
     *
     * @param string|int $key
     * @return void
     */
    public function remove(string|int $key): void
    {
        if (! $this->has($key)) {
            return;
        }
        unset($this->items[$key]);
    }

    public function replace(mixed $newValues, mixed $oldValue) : void
    {
        $key = $this->getKeyByValue($oldValue);
        $this->items[$key] = $newValues;
    }

    public function clear(string $key): void
    {
        $this->items = [];
    }

    public function updateValue(mixed $oldValue, mixed $NewValue) : void
    {
        $key = array_search($oldValue, $this->items, true);
        if ($key === false) {
            throw new BaseException('This value doesnot exsit!');
        }
        if (gettype($oldValue) !== gettype($NewValue)) {
            throw new BaseException('Values are not the same type!');
        }
        $this->items[$key] = $NewValue;
    }

    public function removeByValue(mixed $value) : void
    {
        $key = $this->getKeyByValue($value);
        $this->remove($key);
    }

    public function getKeyByValue(mixed $value) : string
    {
        $key = array_search($value, $this->items, true);
        if ($key === false) {
            throw new BaseException('Cannot remove the value');
        }
        return $key;
    }

    /**
     * Removes duplicate entry from the collection items.
     *
     * @return static
     */
    public function unique(): static
    {
        return new static(array_unique($this->items));
    }

    /**
     * Returns the items in the collection which is not within the specified index array.
     *
     * @param mixed $items
     * @return static
     */
    public function diff(mixed $items): static
    {
        return new static(array_diff($this->items, $items));
    }

    /**
     * Returns the items in the collection which is not within the the specified associative array.
     *
     * @param mixed $items
     * @return static
     */
    public function diffAssoc(mixed $items): static
    {
        return new static(array_diff_assoc($this->items, $items));
    }

    /**
     * Returns the items in the collection whose keys and values is not within the
     * specified associative array, using the callback.
     *
     * @param mixed $items
     * @param callable $callback
     * @return static
     */
    public function diffAssocUsing(mixed $items, callable $callback): static
    {
        return new static(array_diff_uassoc($this->items, $items, $callback));
    }

    /**
     * Returns the items in the collection whose keys in not within the specified
     * index array.
     *
     * @param mixed $items
     * @return static
     */
    public function diffKeys(mixed $items): static
    {
        return new static(array_diff_key($this->items, $items));
    }

    /**
     * Returns the items in the collection whose keys in not within the specified
     * index array, using the callback.
     *
     * @param mixed $items
     * @param callable $callback
     * @return static
     */
    public function diffKeysUsing(mixed $items, callable $callback): static
    {
        return new static(array_diff_ukey($this->items, $items, $callback));
    }

    /**
     * Run a filter over each of the collection item.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(?callable $callback = null): static
    {
        if ($callback) {
            return new static($this->where($this->items, $callback));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Get the first item from the collection passing the given truth test.
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public function first(callable|null $callback = null, $default = null) : mixed
    {
        // return $this-first($this->items, $callback, $default);
        return ! empty($this->items) ? $this->items[0] : [];
    }

    public function last() : mixed
    {
        return end($this->items);
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->map(function ($value) {
            return $value;
        })->all();
    }

    public function offsetExists(mixed $key): bool
    {
        return array_key_exists($key, $this->items) && isset($this->items[$key]);
    }

    public function valueExists(mixed $value, string $keyValue = '') : bool
    {
        if ($keyValue != '') {
            foreach ($this->items as $key => $val) {
                if (property_exists($val, $keyValue) && is_object($value)) {
                    if ($val->$keyValue === $value->$keyValue) {
                        return true;
                    }
                }
            }
        }
        $key = array_search($value, $this->items, true);
        if ($key === false) {
            return false;
        }
        return true;
    }

    public function offsetGet(mixed $key): mixed
    {
        if ($this->has($key)) {
            return $this->items[$key];
        }
        return null;
    }

    public function get(mixed $key): mixed
    {
        return $this->offsetGet($key);
    }

    public function getWithDefault(string $key, mixed $defaultValue) : mixed
    {
        return $this->offsetGet($key) ?? $defaultValue;
    }

    public function offsetSet(mixed $key, mixed $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    public function offsetUnset(mixed $key): void
    {
        unset($this->items[$key]);
    }

    final public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Aliase of $this->size method.
     *
     * @return int
     */
    final public function count(): int
    {
        return $this->size();
    }

    public function flat($array)
    {
        if (! is_array($array)) {
            return false;
        }
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flat($value));
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}