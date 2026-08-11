<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class RestoreProductRegionalPriceListener extends AbstractEntityRestoreListener
{
    public function __construct(
        private ProductRegionalPriceModel $productPrice,
        private RegionContextInterface $regionContext,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($logger);
    }

    protected function expectedEntityClass(): string
    {
        return Product::class;
    }

    protected function entityType(): string
    {
        return 'product';
    }

    protected function performRestore(
        SoftDeletableInterface $entity,
        int|string $entityId,
        DateTimeInterface $archivedAt,
        array $payload,
    ): RestoreResultInterface {
        $regionCode = strtolower((string) ($this->regionContext->getRegionCode() ?? ''));

        if ($regionCode === '') {
            throw new InvalidArgumentException(
                'RestoreProductRegionalPriceListener requires a region code in context.',
            );
        }

        $result = $this->productPrice->restore([
            'product_id' => $entityId,
            'region_code' => $regionCode,
            'archived_at' => $archivedAt,
        ]);

        $this->assertSuccess($result, "regional price (region={$regionCode})", $entityId);

        return new RegionalPriceRestoreResult(
            entityId:     (int) $entityId,
            regionCode:   $regionCode,
            affectedRows: $result->getRowCount(),
            changed:      $result->hasChanged(),
        );
    }
}