<?php

declare(strict_types=1);

/**
 * Fluent builder for contextual bindings.
 */
class ContextualBindingBuilder
{
    private ContextualBindingManager $manager;
    private string $concrete;
    private ?string $needs = null;

    public function __construct(ContextualBindingManager $manager, string $concrete)
    {
        $this->manager = $manager;
        $this->concrete = $concrete;
    }

    /**
     * Specify the abstract that needs to be bound.
     */
    public function needs(string $abstract): self
    {
        $this->needs = $abstract;
        return $this;
    }

    /**
     * Specify the implementation to use.
     */
    public function give(mixed $implementation): void
    {
        if ($this->needs === null) {
            throw new RuntimeException('You must call needs() before give()');
        }

        $this->manager->addContextualBinding($this->concrete, $this->needs, $implementation);
    }
}