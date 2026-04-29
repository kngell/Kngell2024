<?php

declare(strict_types=1);

final class ActionCellConfig
{
    /**
     * @param ActionDefinition[] $actions
     * @param Closure            $idExtractor  fn($entity) => string|int
     */
    public function __construct(
        public readonly array $actions,
        public readonly Closure $idExtractor,
    ) {
    }
}