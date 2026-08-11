<?php

declare(strict_types=1);

final readonly class EntityDescriptor
{
    public function __construct(
        public string $key,           // "category"
        public string $displayName,   // "Category"
        public string $plural,        // "categories"
        public string $basePath,      // "/category-page"
        public string $blockType = '',
    ) {
    }

    public function checkboxName(): string
    {
        return "{$this->plural}[]";
    }

    public function path(string $suffix = ''): string
    {
        return $suffix === '' ? $this->basePath : "{$this->basePath}/{$suffix}";
    }
}