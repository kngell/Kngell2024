<?php

declare(strict_types=1);

final class VariationRestoreResult implements RestoreResultInterface
{
    use RestoreResultTrait;

    public function __construct(
        private int $entityId,
        private int $affectedRows,
        private bool $changed,
        private int $variationsRestored = 0,
        private int $attributesRestored = 0,
        private string $entityType = 'product',
    ) {
    }

    public function variationsRestored(): int
    {
        return $this->variationsRestored;
    }

    public function attributesRestored(): int
    {
        return $this->attributesRestored;
    }
}