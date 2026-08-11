<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class DeleteProductRegionalPriceListener extends AbstractEntityDeletionListener
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

    protected function performDeletion(
        int|string $entityId,
        string $deletionOption,
        array $payload,
    ): DeletionResultInterface {
        $regionCode = strtolower((string) ($this->regionContext->getRegionCode() ?? ''));

        if ($regionCode === '') {
            throw new InvalidArgumentException(
                'DeleteProductRegionalPriceListener requires a region code in context.',
            );
        }

        $result = $this->productPrice->delete([
            'product_id' => $entityId,
            'region_code' => $regionCode,
            'deleteOption' => $deletionOption,
        ]);

        $this->assertSuccess($result, "regional price (region={$regionCode})", $entityId);

        return new RegionalPriceDeletionResult(
            entityId:     (int) $entityId,
            regionCode:   $regionCode,
            affectedRows: $result->getRowCount(),
            changed:      $result->hasChanged(),
            deletionMode: $deletionOption,
        );
    }
}