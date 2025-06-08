<?php

declare(strict_types=1);

interface ContainerInterface
{
    /**
     * Bind Classes, string into to container protected bindings
     * ---------------------------------------------------.
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @param bool $shared
     * @param mixed $parameters
     * @return self
     */
    public function bind(string $abstract, Closure | string | null $concrete = null, bool $shared = false, mixed $parameters = []): self;

    /**
     * Make a unique instance of a class or Closure
     * ----------------------------------------------------.
     * @param string $abstract
     * @param Closure|string|null $concrete
     * @param bool $shared
     * @param mixed $args
     * @return self
     */
    public function singleton(string $abstract, Closure | string | null $concrete = null, bool $shared = true, mixed $args = []): self;

    /**
     * Create a container instance with existing instance
     * -----------------------------------------------------.
     * @param string $abstract
     * @param mixed $instance
     * @return void
     */
    public function instance(string $abstract, mixed $instance): mixed;

    /**
     * empty container
     * -------------------------------------------------------.
     * @return void
     */
    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get(string $id);

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has(string $id): bool;

    public function flush(): void;
}