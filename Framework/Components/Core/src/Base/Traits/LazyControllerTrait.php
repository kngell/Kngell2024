<?php

declare(strict_types=1);

/**
 * @template T
 */
trait LazyControllerTrait
{
    protected App $app;
    private array $lazyDependencies = [];
    private array $resolvedDependencies = [];
    private bool $lazyInitialized = false;

    public function __get(string $name)
    {
        if (isset($this->lazyDependencies[$name])) {
            $instance = $this->lazyDependencies[$name]->getInstance();
            $this->$name = $instance; // Cache it
            return $instance;
        }

        $reflection = CustomReflection::getInstance($this)->getClass();
        if ($reflection->hasProperty($name)) {
            $property = $reflection->getProperty($name);
            $attributes = $property->getAttributes(Inject::class);
            if (!empty($attributes)) {
                /** @var Inject $inject */
                $inject = $attributes[0]->newInstance();
                $class = $inject->getId() ?? $this->getPropertyType($property);

                if ($class) {
                    $instance = $this->app->get($class);
                    $this->$name = $instance;
                    return $instance;
                }
            }
        }

        return null;
    }

    protected function initializeLazyDependencies(App $app): void
    {
        if ($this->lazyInitialized) {
            return;
        }

        $this->app = $app;
        $this->lazyInitialized = true;

        $reflection = CustomReflection::getInstance($this)->getClass();

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(Inject::class);
            foreach ($attributes as $attribute) {
                /** @var Inject $inject */
                $inject = $attribute->newInstance();
                $class = $inject->getId() ?? $this->getPropertyType($property);

                if ($class && $inject->isLazy()) {
                    $this->lazyDependencies[$property->getName()] = new LazyDependencyProxy(
                        $this->app,
                        $class,
                    );

                    // Set the proxy directly
                    $property->setAccessible(true);
                    $property->setValue($this, $this->lazyDependencies[$property->getName()]);
                }
            }
        }
    }

    /**
     * Get a dependency with lazy loading support.
     *
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    protected function getLazy(string $class): object
    {
        // Check if already resolved
        if (isset($this->resolvedDependencies[$class])) {
            return $this->resolvedDependencies[$class];
        }

        // Check if lazy proxy exists
        if (isset($this->lazyDependencies[$class])) {
            $instance = $this->lazyDependencies[$class]->getInstance();
            $this->resolvedDependencies[$class] = $instance;
            return $instance;
        }

        // Resolve directly
        $start = microtime(true);
        $instance = $this->app->get($class);
        $time = (microtime(true) - $start) * 1000;

        if ($time > 50) {
            error_log(sprintf(
                '[DIRECT LOAD] %s loaded in %.2f ms',
                $class,
                $time,
            ));
        }

        $this->resolvedDependencies[$class] = $instance;
        return $instance;
    }

    protected function isLazy(string $name): bool
    {
        return isset($this->lazyDependencies[$name]);
    }

    protected function resolveLazy(string $name): void
    {
        if (isset($this->lazyDependencies[$name])) {
            $this->lazyDependencies[$name]->getInstance();
        }
    }

    private function getPropertyType(ReflectionProperty $property): ?string
    {
        $type = $property->getType();
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $type->getName();
        }
        return null;
    }
}