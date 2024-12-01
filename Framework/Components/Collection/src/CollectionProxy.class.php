<?php

declare(strict_types=1);

class CollectionProxy
{
    /** @var - the collection being used */
    protected $collection;
    /** @var string - the method being proxied */
    protected $method;

    /**
     * Create a new proxy instance.
     *
     * @param CollectionInterface $collection
     * @param string $method
     * @return void
     */
    public function __construct(CollectionInterface $collection, string $method)
    {
        $this->method = $method;
        $this->collection = $collection;
    }

    /**
     * Proxy accessing an attribute onto the collection items.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key): mixed
    {
        return $this->collection->{$this->method}(function ($value) use ($key) {
            return is_array($value) ? $value[$key] : $value->{$key};
        });
    }

    /**
     * Proxy a method call onto the collection items.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters): mixed
    {
        return $this->collection->{$this->method}(function ($value) use ($method, $parameters) {
            return $value->{$method}(...$parameters);
        });
    }
}
