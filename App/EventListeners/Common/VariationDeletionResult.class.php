<?php

declare(strict_types=1);
final class VariationDeletionResult implements DeletionResultInterface
{
    use DeletionResultTrait;

    public function __construct(
        private int $entityId,
        private int $affectedRows,
        private bool $changed,
        private string $deletionMode,
        private int $variationsDeleted = 0,
        private int $attributesDeleted = 0,
        private string $entityType = 'product',
    ) {
    }

    public function variationsDeleted(): int
    {
        return $this->variationsDeleted;
    }

    public function attributesDeleted(): int
    {
        return $this->attributesDeleted;
    }
}