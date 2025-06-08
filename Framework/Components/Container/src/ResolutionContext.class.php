<?php

declare(strict_types=1);

/**
 * Tracks resolution context to prevent circular dependencies.
 */
final class ResolutionContext
{
    private array $resolutionStack = [];
    private array $resolving = [];

    public function startResolving(string $abstract): void
    {
        if (isset($this->resolving[$abstract])) {
            throw ContainerException::circularDependency($abstract, $this->resolutionStack);
        }

        $this->resolving[$abstract] = true;
        $this->resolutionStack[] = $abstract;
    }

    public function finishResolving(string $abstract): void
    {
        unset($this->resolving[$abstract]);
        array_pop($this->resolutionStack);
    }

    public function isResolving(string $abstract): bool
    {
        return isset($this->resolving[$abstract]);
    }

    public function getResolutionStack(): array
    {
        return $this->resolutionStack;
    }

    public function clear(): void
    {
        $this->resolutionStack = [];
        $this->resolving = [];
    }
}