<?php

declare(strict_types=1);

/**
 * Manages tagged services for group resolution.
 * Extracted from Container to centralize tag management.
 */
class ServiceTagManager
{
    /** @var array<string, array<int, string>> */
    private array $tags = [];

    /** @var Container */
    private ContainerInterface $container;

    /** @var DependencyResolver */
    private DependencyResolver $dependencyResolver;

    public function __construct(ContainerInterface $container, DependencyResolver $dependencyResolver)
    {
        $this->container = $container;
        $this->dependencyResolver = $dependencyResolver;
    }

    /**
     * Tag services for group resolution.
     */
    public function tag(string $abstract, array|string $tags): void
    {
        $tags = is_array($tags) ? $tags : [$tags];

        foreach ($tags as $tag) {
            if (!isset($this->tags[$tag])) {
                $this->tags[$tag] = [];
            }
            if (!in_array($abstract, $this->tags[$tag], true)) {
                $this->tags[$tag][] = $abstract;
            }
        }

        // Update tags in dependency resolver
        $this->dependencyResolver->setTags($this->tags);
    }

    /**
     * Resolve all services with a given tag (eager loading).
     */
    public function getTagged(string $tag): array
    {
        if (!isset($this->tags[$tag])) {
            return [];
        }

        $services = [];
        foreach ($this->tags[$tag] as $abstract) {
            $services[] = $this->container->resolve($abstract);
        }

        return $services;
    }

    /**
     * Get a lazy collection for a tag.
     */
    public function getTaggedLazy(string $tag): LazyTagCollection
    {
        $taggedServices = $this->tags[$tag] ?? [];
        return new LazyTagCollection($this->container, $tag, $taggedServices);
    }

    /**
     * Check if a tag exists.
     */
    public function hasTag(string $tag): bool
    {
        return isset($this->tags[$tag]);
    }

    /**
     * Get all tags.
     */
    public function getAllTags(): array
    {
        return array_keys($this->tags);
    }

    /**
     * Get services for a tag without resolving them.
     */
    public function getTaggedAbstracts(string $tag): array
    {
        return $this->tags[$tag] ?? [];
    }

    /**
     * Remove an abstract from all tags.
     */
    public function removeFromTags(string $abstract): void
    {
        foreach ($this->tags as $tagName => $taggedServices) {
            $key = array_search($abstract, $taggedServices, true);
            if ($key !== false) {
                unset($this->tags[$tagName][$key]);
                $this->tags[$tagName] = array_values($this->tags[$tagName]);

                // Remove empty tag arrays
                if (empty($this->tags[$tagName])) {
                    unset($this->tags[$tagName]);
                }
            }
        }

        // Update tags in dependency resolver
        $this->dependencyResolver->setTags($this->tags);
    }

    /**
     * Clear all tags.
     */
    public function clear(): void
    {
        $this->tags = [];
        $this->dependencyResolver->setTags([]);
    }

    /**
     * Get all tag names and their services.
     */
    public function getAllTaggedServices(): array
    {
        return $this->tags;
    }

    /**
     * Tag multiple services at once.
     */
    public function tagMultiple(array $tagMap): void
    {
        foreach ($tagMap as $abstract => $tags) {
            $this->tag($abstract, $tags);
        }
    }
}