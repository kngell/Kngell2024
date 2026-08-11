<?php

declare(strict_types=1);

/**
 * @template T
 */
class LazyDependencyProxy
{
    /**
     * @var T|null
     */
    private ?object $instance = null;

    private bool $resolved = false;
    private array $resolvedDependencies = [];

    /**
     * @param class-string<T> $class
     * @param array<string, mixed> $parameters
     */
    public function __construct(
        private readonly App $app,
        private readonly string $class,
        private readonly array $parameters = [],
    ) {
    }

    /**
     * @param string $method
     * @param array<mixed> $arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->getInstance()->$method(...$arguments);
    }

    /**
     * @param string $property
     *
     * @return mixed
     */
    public function __get(string $property): mixed
    {
        return $this->getInstance()->$property;
    }

    /**
     * @param string $property
     * @param mixed $value
     */
    public function __set(string $property, mixed $value): void
    {
        $this->getInstance()->$property = $value;
    }

    /**
     * @param string $property
     *
     * @return bool
     */
    public function __isset(string $property): bool
    {
        return isset($this->getInstance()->$property);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->getInstance();
    }

    /**
     * @return T
     */
    public function getInstance(): object
    {
        if (!$this->resolved) {
            $start = microtime(true);

            // Build constructor parameters
            $params = $this->resolveParameters($this->parameters);

            // Create instance
            $this->instance = $this->app->resolve($this->class, $params);
            $this->resolved = true;

            $time = (microtime(true) - $start) * 1000;
            if ($time > 10) {
                error_log(sprintf(
                    '[LAZY LOAD] %s instantiated in %.2f ms',
                    $this->class,
                    $time,
                ));
            }
        }

        return $this->instance;
    }

    public function isResolved(): bool
    {
        return $this->resolved;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    private function resolveParameters(array $parameters): array
    {
        $resolved = [];
        foreach ($parameters as $key => $value) {
            if (is_string($value) && $this->app->has($value)) {
                $resolved[$key] = $this->app->get($value);
            } else {
                $resolved[$key] = $value;
            }
        }
        return $resolved;
    }
}