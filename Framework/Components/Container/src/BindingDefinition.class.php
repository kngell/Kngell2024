<?php

declare(strict_types=1);

/**
 * Represents a binding definition in the container.
 */
final readonly class BindingDefinition
{
    public function __construct(
        public string $abstract,
        public mixed $concrete,
        public bool $shared = false,
        public array $parameters = [],
        public ?Closure $factory = null,
        public array $tags = [],
        public ?string $alias = null
    ) {
    }

    public function isShared(): bool
    {
        return $this->shared;
    }

    public function hasParameters(): bool
    {
        return ! empty($this->parameters);
    }

    public function hasFactory(): bool
    {
        return $this->factory !== null;
    }

    public function isClosure(): bool
    {
        return $this->concrete instanceof Closure;
    }

    public function isClass(): bool
    {
        return is_string($this->concrete) && class_exists($this->concrete);
    }

    public function isInterface(): bool
    {
        return is_string($this->concrete) && interface_exists($this->concrete);
    }

    public function isAlias(): bool
    {
        return $this->alias !== null;
    }

    public function hasTags(): bool
    {
        return ! empty($this->tags);
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }

    public function getConcrete(): mixed
    {
        return $this->concrete ?? $this->abstract;
    }

    public function withParameters(array $parameters): self
    {
        return new self(
            $this->abstract,
            $this->concrete,
            $this->shared,
            array_merge($this->parameters, $parameters),
            $this->factory,
            $this->tags,
            $this->alias
        );
    }

    public function withTags(array $tags): self
    {
        return new self(
            $this->abstract,
            $this->concrete,
            $this->shared,
            $this->parameters,
            $this->factory,
            array_merge($this->tags, $tags),
            $this->alias
        );
    }
}