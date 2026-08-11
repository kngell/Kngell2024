<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_CLASS)]
class SelectOptionConfig
{
    public function __construct(
        public readonly string $selectLabel = '-- Select an option --',
        public readonly ?string $entityClass = null,
        public readonly ?string $idMethod = 'getId',
        public readonly ?string $labelMethod = null,
        public readonly array $defaultOptions = [],
    ) {
    }
}