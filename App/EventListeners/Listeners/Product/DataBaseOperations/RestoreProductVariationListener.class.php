<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class RestoreProductVariationListener extends AbstractEntityRestoreListener
{
    public function __construct(
        private ProductVariationModel $productVariationModel,
        private VariationAttributeModel $variationAttributeModel,
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
        $productId = (int) $entityId;

        $variationIds = $this->collectCohortVariationIds($productId, $archivedAt);

        // 2) Restore variations.
        $varResult = $this->productVariationModel->restore([
            'product_id' => $productId,
            'archived_at' => $archivedAt,
        ]);
        $this->assertSuccess($varResult, 'variations', $productId);

        // 3) Restore attributes scoped to the same timestamp.
        $attributesRestored = 0;
        if ($variationIds !== []) {
            $attrResult = $this->variationAttributeModel->restore([
                'variation_id' => $variationIds,
                'archived_at' => $archivedAt,
            ]);
            $this->assertSuccess($attrResult, 'variation attributes', $productId);
            $attributesRestored = $attrResult->getRowCount();
        }

        return new VariationRestoreResult(
            entityId:           $productId,
            affectedRows:       $varResult->getRowCount() + $attributesRestored,
            changed:            $varResult->hasChanged() || $attributesRestored > 0,
            variationsRestored: $varResult->getRowCount(),
            attributesRestored: $attributesRestored,
        );
    }

    /**
     * @return list<int>
     */
    private function collectCohortVariationIds(int $productId, DateTimeInterface $archivedAt): array
    {
        $rows = $this->productVariationModel
            ->all([
                'product_id' => $productId,
                'deleted_at' => $archivedAt->format('Y-m-d H:i:s'),
            ])
            ->asArray();

        return array_values(array_filter(
            array_map('intval', array_column($rows, 'id')),
            static fn (int $id): bool => $id > 0,
        ));
    }
}