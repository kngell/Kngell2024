<?php

declare(strict_types=1);
final class RegionalPriceDeletionResult implements DeletionResultInterface
{
    use DeletionResultTrait;

    public function __construct(
        private int $entityId,
        private string $regionCode,
        private int $affectedRows,
        private bool $changed,
        private string $deletionMode,
        private string $entityType = 'product',
    ) {
    }

    public function regionCode(): string
    {
        return $this->regionCode;
    }
}